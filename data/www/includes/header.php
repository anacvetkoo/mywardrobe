<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? "MyWardrobe"; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/stil.css">

    <!-- React -->
    <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>

    <!-- Babel za JSX -->
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

    
</head>

<body>

<header class="container py-3 mb-4 border-bottom">
    <div class="d-flex align-items-center justify-content-between">

        <!-- Logo -->
        <a href="/index.php" class="d-flex align-items-center text-dark text-decoration-none">
            <img src="/slike/logo.png" alt="MyWardrobe logo" width="150">
        </a>

        <!-- Navigacija (desktop) -->
        <div class="d-flex justify-content-center flex-grow-1">
            <nav class="d-none d-lg-flex align-items-center">
                <a class="nav-link me-3" href="/podstrani/kategorija.php?tip=oblacila">Oblačila</a>
                <a class="nav-link me-3" href="/podstrani/kategorija.php?tip=obutev">Obutev</a>
                <a class="nav-link me-3" href="/podstrani/kategorija.php?tip=dodatki">Dodatki</a>
                <a class="nav-link me-3" href="/podstrani/kategorija.php">Vse</a>
            </nav>
        </div>

        <!-- Search bar (desktop) -->
        <form class="d-none d-lg-flex mx-3" role="search" action="/podstrani/search.php" method="get">
            <input class="form-control me-2" type="search" name="q" placeholder="Išči izdelke..." aria-label="Search">
            <button class="btn btn-outline-dark" type="submit"><i class="bi bi-search" style="color:grey;"></i></button>
        </form>

        <!-- Ikone (desktop) -->
        <div class="d-flex align-items-center d-none d-lg-flex">
            <?php if (isset($_SESSION["uporabnik_id"])): ?>
                <a href="/podstrani/profil.php?id=<?= $_SESSION["uporabnik_id"] ?>" class="me-3">
                    <i class="bi bi-person"></i>
                </a>
            <?php else: ?>
                <a href="/podstrani/prijava.php" class="me-3">
                    <i class="bi bi-person"></i>
                </a>
            <?php endif; ?>
            <a href="/podstrani/nastavitve.php" class="me-3">
                <i class="bi bi-brilliance"></i>
            </a>
            <a href="/podstrani/prodaj.php" class="btn-secondary">Prodaj zdaj</a>
        </div>

        <!-- Hamburger (mobile) -->
        <button class="navbar-toggler d-lg-none border-0 ms-auto"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navMenu"
                aria-controls="navMenu"
                aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="fs-2">☰</span>
        </button>
    </div>

    <!-- Mobile meni -->
    <div class="collapse" id="navMenu">
        <nav class="nav nav-pills flex-column text-center py-3">
            <a class="nav-link text-dark me-3" href="/podstrani/kategorija.php?tip=oblacila">Oblačila</a>
            <a class="nav-link text-dark me-3" href="/podstrani/kategorija.php?tip=obutev">Obutev</a>
            <a class="nav-link text-dark me-3" href="/podstrani/kategorija.php?tip=dodatki">Dodatki</a>
            <a class="nav-link text-dark me-3" href="/podstrani/kategorija.php">Vse</a>

            <!-- Search bar (mobile) -->
            <form class="d-flex my-3 justify-content-center" role="search" action="/podstrani/search.php" method="get">
                <input class="form-control me-2" type="search" name="q" placeholder="Išči izdelke..." aria-label="Search">
                <button class="btn btn-outline-dark" type="submit"><i class="bi bi-search" style="color:grey;"></i></button>
            </form>

            <div class="d-flex justify-content-center mt-3 d-lg-none">
                <?php if (isset($_SESSION["uporabnik_id"])): ?>
                    <a href="/podstrani/profil.php?id=<?= $_SESSION["uporabnik_id"] ?>" class="me-3">
                        <i class="bi bi-person"></i>
                    </a>
                <?php else: ?>
                    <a href="/podstrani/prijava.php" class="me-3">
                        <i class="bi bi-person"></i>
                    </a>
                <?php endif; ?>
                <a href="/podstrani/nastavitve.php" class="me-3">
                    <i class="bi bi-brilliance"></i>
                </a>
                <a href="/podstrani/prodaj.php" class="btn-secondary">Prodaj zdaj</a>
            </div>
        </nav>
    </div>
</header>