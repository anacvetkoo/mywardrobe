<?php
require_once "db.php";
require_once "session.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/phpmailer/PHPMailer.php";
require_once __DIR__ . "/phpmailer/SMTP.php";
require_once __DIR__ . "/phpmailer/Exception.php";

header("Content-Type: application/json");

// preveri prijavo
if (!isset($_SESSION["uporabnik_id"])) {
    echo json_encode(["success" => false, "error" => "Nisi prijavljen."]);
    exit;
}

// preberi JSON
$data = json_decode(file_get_contents("php://input"), true);

$produkt_id = (int)($data["produkt_id"] ?? 0);
$sporocilo  = trim($data["sporocilo"] ?? "");

if ($produkt_id === 0 || $sporocilo === "") {
    echo json_encode(["success" => false, "error" => "Manjkajo podatki."]);
    exit;
}

// dobi email prodajalca
$stmt = $pdo->prepare("
    SELECT u.uporabnisko_ime
    FROM Produkt p
    JOIN Uporabnik u ON p.TK_uporabnik = u.id_uporabnik
    WHERE p.id_produkt = ?
");
$stmt->execute([$produkt_id]);
$prodajalec = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prodajalec) {
    echo json_encode(["success" => false, "error" => "Produkt ne obstaja."]);
    exit;
}


// dobi email pošiljatelja
$stmt = $pdo->prepare("SELECT uporabnisko_ime FROM Uporabnik WHERE id_uporabnik = ?");
$stmt->execute([$_SESSION["uporabnik_id"]]);
$posiljatelj = $stmt->fetch(PDO::FETCH_ASSOC);

// pošlji mail
$mail = new PHPMailer(true);

try {
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

    $mail->setFrom('ana.cvetko2005@gmail.com', "MyWardrobe");
    $mail->addAddress($prodajalec["uporabnisko_ime"]);

    $mail->Subject = "Novo sporočilo - MyWardrobe";
    $mail->Body =
        "Uporabnik " . $posiljatelj["uporabnisko_ime"] .
        " vam je poslal/a sporočilo:\n\n" .
        $sporocilo;

    $mail->send();
    $mail->smtpClose();

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Napaka pri pošiljanju maila."]);
}
