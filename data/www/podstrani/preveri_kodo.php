<?php
require_once "../includes/session.php";
require_once "../includes/db.php";

// Če uporabnik ni prišel iz prijave
if (!isset($_SESSION["2fa_uporabnik"])) {
    header("Location: prijava.php");
    exit;
}

$napaka = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $vnesenaKoda = trim($_POST["koda"] ?? "");

    if ($vnesenaKoda === "") {
        $napaka = "Vnesi kodo.";
    } else {

        // Preveri kodo v bazi
        $stmt = $pdo->prepare("
            SELECT 
                id_uporabnik,
                koda_2fa,
                koda_2fa_potece,
                TK_tip_uporabnika,
                aktiven
            FROM Uporabnik
            WHERE id_uporabnik = ?
        ");
        $stmt->execute([$_SESSION["2fa_uporabnik"]]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$u) {
            $napaka = "Napaka pri preverjanju uporabnika.";
        } elseif ($u["koda_2fa"] !== $vnesenaKoda) {
            $napaka = "Napačna koda.";
        } elseif (strtotime($u["koda_2fa_potece"]) < time()) {
            $napaka = "Koda je potekla. Prijavi se ponovno.";
        } else {
            // aktiviraj uporabnika
            $pdo->prepare("
                UPDATE Uporabnik
                SET aktiven = 1,
                    koda_2fa = NULL,
                    koda_2fa_potece = NULL
                WHERE id_uporabnik = ?
            ")->execute([$u["id_uporabnik"]]);

            // počisti session
            unset($_SESSION["2fa_uporabnik"]);

            // preusmeri na prijavo
            header("Location: prijava.php?msg=registriran");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preveri kodo – MyWardrobe</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/stil.css">
</head>

<body class="d-flex align-items-center justify-content-center" style="min-height:100vh; background:#000; color:#fff;">

<div class="card p-4" style="max-width:400px; width:100%; background:#111; color:white;">
    <h4 class="text-center mb-3">Vnesi prijavno kodo</h4>

    <p class="text-center" style="font-size:0.9rem;">
        Na tvojo e-pošto smo poslali 6-mestno kodo.<br>
        Koda velja <strong>10 minut</strong>.
    </p>

    <?php if ($napaka): ?>
        <div class="alert alert-danger text-center">
            <?= htmlspecialchars($napaka) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <input
                type="text"
                name="koda"
                class="form-control text-center"
                placeholder="123456"
                maxlength="6"
                required
            >
        </div>

        <button type="submit" class="btn btn-dark w-100">
            Potrdi kodo
        </button>
    </form>

    <div class="text-center mt-3">
        <a href="prijava.php" class="text-decoration-underline " style="font-size:0.85rem; color:white;">
            Nazaj na prijavo
        </a>
    </div>
</div>

</body>
</html>
