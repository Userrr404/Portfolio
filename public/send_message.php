<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header("Content-Type: application/json; charset=utf-8");

// --- Helper functions ---
function json_error($msg) {
    echo json_encode(["status" => "error", "message" => $msg]);
    exit;
}

function json_success($msg) {
    echo json_encode(["status" => "success", "message" => $msg]);
    exit;
}

// Read inputs
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');
$honeypot = trim($_POST['hp_name'] ?? '');
$recaptcha_token = trim($_POST['recaptcha_token'] ?? '');
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// --- 1) Honeypot check (simple & effective) ---
if ($honeypot !== '') {
    // treat as spam silently
    json_error("Spam detected");
}

// --- 2) Basic server-side validation ---
if ($name === '' || $email === '' || $message === '') {
    json_error("Please fill all fields ❗");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error("Invalid email address ❗");
}

// --- 3) Rate limiting (per-IP)
//    Policy: 1 message / 60s, max 5 / hour
try {
    $pdo = DB::getInstance()->pdo();
} catch (Throwable $e) {
    json_error("Server error (DB).");
}

// check last 60 seconds
$limitWindowSeconds = 60;
$maxPerHour = 5;

try {
    // Count messages in last minute and last hour
    $stmt = $pdo->prepare("SELECT 
            SUM(created_at >= (NOW() - INTERVAL 60 SECOND)) AS last_minute,
            SUM(created_at >= (NOW() - INTERVAL 1 HOUR)) AS last_hour
        FROM contact_messages
        WHERE ip_address = :ip
    ");
    $stmt->execute([':ip' => $ip]);
    $counts = $stmt->fetch(PDO::FETCH_ASSOC);

    $last_minute = intval($counts['last_minute'] ?? 0);
    $last_hour = intval($counts['last_hour'] ?? 0);

    if ($last_minute >= 1) {
        json_error("You're sending messages too quickly. Please wait a moment.");
    }
    if ($last_hour >= $maxPerHour) {
        json_error("Message limit reached. Try again later.");
    }
} catch (Throwable $e) {
    // If counting fails, allow but log
    app_log("Rate limit check failed: " . $e->getMessage(), "warning");
}

// --- 4) reCAPTCHA v3 verification (if configured)
// If RECAPTCHA_SECRET_KEY is not defined, skip verification (useful for local)
if (defined('RECAPTCHA_SECRET_KEY') && RECAPTCHA_SECRET_KEY) {
    if (empty($recaptcha_token)) {
        json_error("reCAPTCHA verification missing. Please try again.");
    }
    // Verify with Google
    $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $payload = http_build_query([
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $recaptcha_token,
        'remoteip' => $ip
    ]);

    $opts = [
        "http" => [
            "method" => "POST",
            "header" => "Content-Type: application/x-www-form-urlencoded\r\n",
            "content" => $payload,
            "timeout" => 5
        ]
    ];
    $context = stream_context_create($opts);
    $resp = @file_get_contents($verifyUrl, false, $context);
    if ($resp === false) {
        app_log("reCAPTCHA verify HTTP failure", "warning");
        json_error("reCAPTCHA verification failed. Please try again.");
    }
    $respData = json_decode($resp, true);
    // v3 returns score; allow > 0.5 (tunable)
    if (!($respData['success'] ?? false)) {
        app_log("reCAPTCHA failed: " . json_encode($respData), "warning");
        json_error("reCAPTCHA verification failed.");
    }
    $score = floatval($respData['score'] ?? 0);
    $action = $respData['action'] ?? '';
    // Tunable thresholds:
    if ($score < 0.4) {
        app_log("reCAPTCHA low score ({$score}) for IP {$ip}", "warning");
        json_error("Your submission looks suspicious. Please try again.");
    }
}

// --- 5) Insert message to DB (initial record, email_sent=0) ---
try {
    $stmt = $pdo->prepare("INSERT INTO contact_messages
        (name, email, message, ip_address, user_agent, email_sent, created_at)
        VALUES (:name, :email, :message, :ip, :ua, 0, NOW())");
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':message' => $message,
        ':ip' => $ip,
        ':ua' => $userAgent
    ]);
    $messageId = $pdo->lastInsertId();
} catch (Throwable $e) {
    app_log("DB insert contact_messages failed: " . $e->getMessage(), "error");
    json_error("Database error occurred ❌");
}

// --- 6) Send email (PHPMailer) and update record with result ---
try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = EMAIL_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = EMAIL_USER;
    $mail->Password = EMAIL_PASS;
    $mail->SMTPSecure = 'tls';
    $mail->Port = EMAIL_PORT;

    // From: your site address (avoid Gmail spoof issues)
    $mail->setFrom(EMAIL_USER, SITE_NAME . " Contact");
    // Reply-to user (so owner can reply directly)
    $mail->addReplyTo($email, $name);
    $mail->addAddress(EMAIL_USER); // owner

    $mail->isHTML(true);
    $mail->Subject = "New contact message from {$name}";
    $body = "<h3>New contact message</h3>";
    $body .= "<p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>";
    $body .= "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
    $body .= "<p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>";
    $body .= "<hr><p>IP: {$ip}</p>";
    $mail->Body = $body;

    $mail->send();

    // mark email_sent = 1
    $stmt = $pdo->prepare("UPDATE contact_messages SET email_sent = 1, email_error = NULL WHERE id = :id");
    $stmt->execute([':id' => $messageId]);

    json_success("Message sent successfully 🎉");
} catch (Throwable $e) {
    // Update record with error text (truncate)
    $err = substr((string)$e->getMessage(), 0, 1000);
    try {
        $stmt = $pdo->prepare("UPDATE contact_messages SET email_sent = 0, email_error = :err WHERE id = :id");
        $stmt->execute([':err' => $err, ':id' => $messageId]);
    } catch (Throwable $e2) {
        app_log("Failed to update email_error: " . $e2->getMessage(), "warning");
    }
    app_log("PHPMailer send failed: " . $err, "error");
    json_error("Email sending failed ❌");
}
?>
