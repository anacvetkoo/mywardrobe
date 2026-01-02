<?php
require_once __DIR__ . "/session.php";
require_once __DIR__ . "/db.php";

if (!jeAdmin()) {
    header("Location: /index.php");
    exit;
}

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($id <= 0) {
    header("Location: /podstrani/admin_izdelki.php");
    exit;
}

$stmt = $pdo->prepare("DELETE FROM Produkt WHERE id_produkt = ?");
$stmt->execute([$id]);

header("Location: /podstrani/admin_izdelki.php");
exit;
?>