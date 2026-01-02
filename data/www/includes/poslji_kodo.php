<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/phpmailer/Exception.php";
require_once __DIR__ . "/phpmailer/PHPMailer.php";
require_once __DIR__ . "/phpmailer/SMTP.php";

function poslji2FAKodo($email, $koda) {

    $mail = new PHPMailer(true);

    try {
        // SMTP nastavitve
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ];
        $mail->Username = 'ana.cvetko2005@gmail.com';   
        $mail->Password = 'uxqlbijuswiojmup';          
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->SMTPKeepAlive = true;
        $mail->Timeout = 20;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        // Pošiljatelj + prejemnik
        $mail->setFrom('ana.cvetko2005@gmail.com', 'MyWardrobe');
        $mail->addAddress($email);

        // Vsebina
        $mail->isHTML(true);
        $mail->Subject = 'Potrditev registracije – MyWardrobe';
        $mail->Body = "
            <h3>Potrditev registracije</h3>
            <p>Tvoja potrditvena koda je:</p>
            <h2>$koda</h2>
            <p>Koda velja 10 minut.</p>
        ";

        

        $mail->send();
        $mail->smtpClose();

    } catch (Exception $e) {
        die("Napaka pri pošiljanju e-pošte: {$mail->ErrorInfo}");
    }
}