<?php
require_once __DIR__ . "/session.php";
require_once __DIR__ . "/db.php";

if (!jeAdmin()) {
    header("Location: /index.php");
    exit;
}

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($id <= 0) {
    header("Location: /podstrani/admin_uporabniki.php");
    exit;
}

// admin ne sme spreminjati samega sebe
if (isset($_SESSION["uporabnik_id"]) && $id === (int)$_SESSION["uporabnik_id"]) {
    header("Location: /podstrani/admin_uporabniki.php");
    exit;
}

// 1 -> 3
$stmt = $pdo->prepare("UPDATE Uporabnik SET TK_tip_uporabnika = 3 WHERE id_uporabnik = ?");
$stmt->execute([$id]);

header("Location: /podstrani/admin_uporabniki.php");
exit;
?>