<?php
namespace app\Models;

use app\Core\DB;
use Throwable;

class ContactMessageModel
{
    public function checkRateLimit(string $ip): void
    {
        $pdo = DB::getInstance()->pdo();

        $stmt = $pdo->prepare("
            SELECT 
              SUM(created_at >= NOW() - INTERVAL 60 SECOND) AS last_minute,
              SUM(created_at >= NOW() - INTERVAL 1 HOUR) AS last_hour
            FROM contact_messages
            WHERE ip_address = :ip
        ");
        $stmt->execute([':ip' => $ip]);
        $c = $stmt->fetch();

        if (($c['last_minute'] ?? 0) >= 1) {
            throw new \Exception("You're sending messages too quickly. Please wait.");
        }
        if (($c['last_hour'] ?? 0) >= 5) {
            throw new \Exception("Message limit reached. Try again later.");
        }
    }

    public function verifyRecaptcha(string $token, string $ip): void
    {
        if (!defined('RECAPTCHA_SECRET_KEY') || !RECAPTCHA_SECRET_KEY) return;

        if ($token === '') {
            throw new \Exception("reCAPTCHA verification missing.");
        }

        $resp = file_get_contents(
            'https://www.google.com/recaptcha/api/siteverify',
            false,
            stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'content' => http_build_query([
                        'secret'   => RECAPTCHA_SECRET_KEY,
                        'response' => $token,
                        'remoteip' => $ip
                    ]),
                    'timeout' => 5
                ]
            ])
        );

        $data = json_decode($resp, true);

        if (!($data['success'] ?? false) || ($data['score'] ?? 0) < 0.4) {
            app_log("reCAPTCHA failed", "warning", $data ?? []);
            throw new \Exception("Your submission looks suspicious. Please try again.");
        }
    }

    public function storeMessage(array $d): int
    {
        $pdo = DB::getInstance()->pdo();

        $stmt = $pdo->prepare("
            INSERT INTO contact_messages
            (name,email,message,ip_address,user_agent,email_sent,created_at)
            VALUES (:n,:e,:m,:ip,:ua,0,NOW())
        ");
        $stmt->execute([
            ':n'  => $d['name'],
            ':e'  => $d['email'],
            ':m'  => $d['message'],
            ':ip' => $d['ip'],
            ':ua' => $d['ua'],
        ]);

        return (int)$pdo->lastInsertId();
    }

    public function markEmailSuccess(int $id): void
    {
        DB::getInstance()->pdo()
            ->prepare("UPDATE contact_messages SET email_sent = 1, email_error = NULL WHERE id = ?")
            ->execute([$id]);
    }
}
