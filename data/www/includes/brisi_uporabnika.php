<?php
require_once __DIR__ . "/session.php";
require_once __DIR__ . "/db.php";

if (!jeAdmin()) {
    header("Location: /index.php");
    exit;
}

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$akcija = $_GET["akcija"] ?? "";

if ($id <= 0 || !in_array($akcija, ["aktiviraj", "deaktiviraj"])) {
    header("Location: /podstrani/admin_uporabniki.php");
    exit;
}

// admin ne sme deaktivirati samega sebe
if ($id === (int)$_SESSION["uporabnik_id"]) {
    header("Location: /podstrani/admin_uporabniki.php");
    exit;
}

if ($akcija === "deaktiviraj") {
    $stmt = $pdo->prepare("UPDATE Uporabnik SET aktiven = 0 WHERE id_uporabnik = ?");
} else {
    $stmt = $pdo->prepare("UPDATE Uporabnik SET aktiven = 1 WHERE id_uporabnik = ?");
}

$stmt->execute([$id]);

header("Location: /podstrani/admin_uporabniki.php");
exit;