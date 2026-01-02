<?php
require_once "../includes/session.php";
require_once "../includes/db.php";

$napaka = "";

if (isset($_GET["msg"]) && $_GET["msg"] === "obstaja") {
    $napaka = "Uporabnik že obstaja. Prosim prijavi se.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $geslo = $_POST["geslo"] ?? "";

    if ($email === "" || $geslo === "") {
        $napaka = "Vnesi e-pošto in geslo.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $napaka = "E-pošta ni veljavna.";
    } else {
        $stmt = $pdo->prepare("SELECT id_uporabnik, geslo, TK_tip_uporabnika, aktiven FROM Uporabnik WHERE uporabnisko_ime = ?");
        $stmt->execute([$email]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        // SHA preverjanje
        $vnosHash = hash('sha256', $geslo);

        if ($u && $u["aktiven"] && hash_equals($u["geslo"], $vnosHash)) {
            $_SESSION["uporabnik_id"] = $u["id_uporabnik"];
            $_SESSION["tip"] = (int)$u["TK_tip_uporabnika"];
            header("Location: profil.php?id=" . $u["id_uporabnik"]);
            exit;
        } else {
            $napaka = "Napačna e-pošta ali geslo.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prijava - MyWardrobe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/stil.css">
</head>
<body class="d-flex flex-column align-items-center justify-content-center" style="min-height:100vh; background:#000; color:#fff;">

    <a href="../index.php"><img src="/slike/logo.png" alt="MyWardrobe logo" class="mb-4" style="width:150px;"></a>
    <h2 class="mb-4">PRIJAVA</h2>

    <?php if (!empty($napaka)): ?>
    <div class="alert alert-danger w-100" style="max-width:400px;">
        <?php echo htmlspecialchars($napaka); ?>
    </div>
    <?php endif; ?>

    <form class="w-100" style="max-width:400px;" action="" method="post">
        <div class="mb-3">
            <label for="email" class="form-label">Uporabniško ime</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="geslo" class="form-label">Geslo</label>
            <input type="password" class="form-control" id="geslo" name="geslo" required>
        </div>
        <button type="submit" class="btn btn-dark w-100">Prijava</button>
    </form>

    <p class="mt-3">
        <a href="registracija.php" class="text-decoration-underline text-white">Nimam še računa - registriraj se</a>
    </p>

</body>
</html>