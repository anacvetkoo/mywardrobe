<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function jePrijavljen() {
    return isset($_SESSION["uporabnik_id"]);
}

function jeAdmin() {
    return isset($_SESSION["tip"]) && $_SESSION["tip"] == 3;
}