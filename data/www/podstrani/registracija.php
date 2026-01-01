<?php
require_once "../includes/session.php";
require_once "../includes/db.php";

$napaka = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ime = trim($_POST["ime"] ?? "");
    $priimek = trim($_POST["priimek"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $geslo = $_POST["geslo"] ?? "";
    $geslo2 = $_POST["geslo2"] ?? "";

    // VALIDACIJA
    if ($ime === "" || $priimek === "" || $email === "" || $geslo === "" || $geslo2 === "") {
        $napaka = "Prosim izpolni vsa polja.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $napaka = "E-poštni naslov ni veljaven.";
    } elseif (strlen($geslo) < 6) {
        $napaka = "Geslo naj ima vsaj 6 znakov.";
    } elseif ($geslo !== $geslo2) {
        $napaka = "Gesli se ne ujemata.";
    } else {
        // preveri, če email že obstaja (email = uporabniško ime -> uporabnisko_ime)
        $check = $pdo->prepare("SELECT id_uporabnik FROM Uporabnik WHERE uporabnisko_ime = ?");
        $check->execute([$email]);
        $obstaja = $check->fetch(PDO::FETCH_ASSOC);

        if ($obstaja) {
            // uporabnik že obstaja -> preusmeri na prijavo + sporočilo
            header("Location: prijava.php?msg=obstaja");
            exit;
        }

        // SHA-256 hash gesla
        $hash = hash('sha256', $geslo);

        // v tvoji bazi je uporabnisko_ime, zato tja shranimo email
        $ins = $pdo->prepare("
            INSERT INTO Uporabnik (ime, priimek, uporabnisko_ime, geslo, TK_tip_uporabnika)
            VALUES (?, ?, ?, ?, ?)
        ");
        $ins->execute([$ime, $priimek, $email, $hash, 1]);

        // AUTO LOGIN po registraciji
        $novId = $pdo->lastInsertId();
        $_SESSION["uporabnik_id"] = $novId;

        header("Location: profil.php?id=" . $novId);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registracija - MyWardrobe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/stil.css">
</head>
<body class="d-flex flex-column align-items-center justify-content-center" style="min-height:100vh; background:#000; color:#fff;">

    <a href="../index.php"><img src="/slike/logo.png" alt="MyWardrobe logo" class="mb-4" style="width:150px;"></a>
    <h2 class="mb-4">REGISTRACIJA</h2>

    <?php if (!empty($napaka)): ?>
    <div class="alert alert-danger w-100" style="max-width:400px;">
        <?php echo htmlspecialchars($napaka); ?>
    </div>
    <?php endif; ?>

    <form class="w-100" style="max-width:400px;" action="" method="post">
        <div class="mb-3">
            <label for="ime" class="form-label">Ime</label>
            <input type="text" class="form-control" id="ime" name="ime" required>
        </div>
        <div class="mb-3">
            <label for="priimek" class="form-label">Priimek</label>
            <input type="text" class="form-control" id="priimek" name="priimek" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">E-poštni naslov</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="geslo" class="form-label">Geslo</label>
            <input type="password" class="form-control" id="geslo" name="geslo" required>
        </div>
        <div class="mb-3">
            <label for="geslo2" class="form-label">Ponovi geslo</label>
            <input type="password" class="form-control" id="geslo2" name="geslo2" required>
        </div>
        <button type="submit" class="btn btn-dark w-100">Registriraj se</button>
    </form>

    <p class="mt-3">
        <a href="prijava.php" class="text-decoration-underline text-white">Že imaš račun? Prijavi se</a>
    </p>

</body>
</html>