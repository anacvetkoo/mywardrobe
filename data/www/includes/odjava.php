<?php
require_once "session.php";

// počisti session
$_SESSION = [];

// uniči session
session_destroy();

// preusmeri na index
header("Location: /index.php");
exit;