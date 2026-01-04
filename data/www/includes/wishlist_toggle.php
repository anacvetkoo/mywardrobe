<?php
require_once __DIR__ . "/session.php";
require_once __DIR__ . "/db.php";

header("Content-Type: application/json; charset=UTF-8");

// mora biti prijavljen
if (!isset($_SESSION["uporabnik_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Za wishlist morate biti prijavljeni."]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$produkt_id = isset($input["produkt_id"]) ? (int)$input["produkt_id"] : 0;

if ($produkt_id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Manjka ID produkta."]);
    exit;
}

$uporabnik_id = (int)$_SESSION["uporabnik_id"];

// preveri ali izdelek sploh obstaja
$chk = $pdo->prepare("SELECT id_produkt FROM Produkt WHERE id_produkt = ?");
$chk->execute([$produkt_id]);
if (!$chk->fetch()) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Produkt ne obstaja."]);
    exit;
}

// ali je že v wishlistu?
$exists = $pdo->prepare("SELECT 1 FROM Wishlist WHERE TK_uporabnik = ? AND TK_produkt = ?");
$exists->execute([$uporabnik_id, $produkt_id]);

if ($exists->fetchColumn()) {
    // odstrani
    $del = $pdo->prepare("DELETE FROM Wishlist WHERE TK_uporabnik = ? AND TK_produkt = ?");
    $del->execute([$uporabnik_id, $produkt_id]);

    echo json_encode(["success" => true, "action" => "removed"]);
    exit;
}

// dodaj
$ins = $pdo->prepare("INSERT INTO Wishlist (TK_uporabnik, TK_produkt) VALUES (?, ?)");
$ins->execute([$uporabnik_id, $produkt_id]);

echo json_encode(["success" => true, "action" => "added"]);
exit;
