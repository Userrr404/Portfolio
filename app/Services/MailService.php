<?php
namespace app\Services;

use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    public static function sendContactMail(array $d): void
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = EMAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = EMAIL_USER;
        $mail->Password   = EMAIL_PASS;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = EMAIL_PORT;

        $mail->setFrom(EMAIL_USER, SITE_NAME . " Contact");
        $mail->addReplyTo($d['email'], $d['name']);
        $mail->addAddress(EMAIL_USER);

        $mail->isHTML(true);
        $mail->Subject = "New contact message from {$d['name']}";
        $mail->Body = "
            <h3>New Contact Message</h3>
            <p><b>Name:</b> {$d['name']}</p>
            <p><b>Email:</b> {$d['email']}</p>
            <p><b>Message:</b> {$d['message']}</p>
            <hr>
            <p>IP: {$d['ip']}</p>
        ";

        $mail->send();
    }
}
