<?php
require_once "../includes/session.php";
require_once "../includes/db.php";
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

    <form class="w-100" style="max-width:400px;" action="registracija_handler.php" method="post">
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
        <button type="submit" class="btn btn-dark w-100">Registriraj se</button>
    </form>

    <p class="mt-3">
        <a href="prijava.php" class="text-decoration-underline text-white">Že imaš račun? Prijavi se</a>
    </p>

</body>
</html>