<?php
$host = "podatkovna-baza";
$dbname = "mywardrobe";
$username = "root";
$password = "promoa2U.";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Povezava ni uspela: " . $e->getMessage());
}