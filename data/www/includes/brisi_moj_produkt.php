<?php
require_once __DIR__ . "/session.php";
require_once __DIR__ . "/db.php";

if (!isset($_SESSION["uporabnik_id"])) {
    die("Niste prijavljeni.");
}

$produkt_id = $_GET["id"] ?? null;
$uporabnik_id = $_SESSION["uporabnik_id"];

if (!$produkt_id) {
    die("Manjka ID izdelka.");
}

// preveri lastništvo + pridobi sliko
$stmt = $pdo->prepare("
    SELECT slika
    FROM Produkt
    WHERE id_produkt = ? AND TK_uporabnik = ?
");
$stmt->execute([$produkt_id, $uporabnik_id]);
$produkt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produkt) {
    die("Izdelek ne obstaja ali ni vaš.");
}

// izbriši sliko iz diska
if (!empty($produkt["slika"])) {
    $pot = __DIR__ . "/../" . $produkt["slika"];
    if (file_exists($pot)) {
        unlink($pot);
    }
}

// izbriši iz baze
$del = $pdo->prepare("DELETE FROM Produkt WHERE id_produkt = ?");
$del->execute([$produkt_id]);

header("Location: /podstrani/profil.php?id=" . $uporabnik_id);
exit;