<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

use PHPMailer\PHPMailer\PHPMailer;

header("Content-Type: application/json");

// Sanitize inputs
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$name || !$email || !$message) {
    echo json_encode(["status" => "error", "message" => "Please fill all fields ❗"]);
    exit;
}

// Store message
try {
    $pdo = DB::getInstance()->pdo();
    $stmt = $pdo->prepare("
        INSERT INTO contact_messages (name, email, message)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$name, $email, $message]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Database error ❌"]);
    exit;
}

// Send email
try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = EMAIL_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = EMAIL_USER;
    $mail->Password = EMAIL_PASS;
    $mail->SMTPSecure = 'tls';
    $mail->Port = EMAIL_PORT;

    $mail->setFrom($email, $name);
    $mail->addAddress('yogeshlilakedev02@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = "New Contact Form Message";
    $mail->Body = "
        <h3>New Contact Message</h3>
        <p><strong>Name:</strong> {$name}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Message:</strong><br>{$message}</p>
    ";

    $mail->send();

    echo json_encode(["status" => "success", "message" => "Message sent successfully 🎉"]);
    exit;

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Email sending failed ❌"]);
    exit;
}
?>
