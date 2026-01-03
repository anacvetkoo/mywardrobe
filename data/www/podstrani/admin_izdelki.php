<?php
require_once "../includes/session.php";
require_once "../includes/db.php";

if (!jeAdmin()) {
    header("Location: ../index.php");
    exit;
}

// vsi izdelki
$stmt = $pdo->query("
    SELECT id_produkt, naziv, cena, slika
    FROM Produkt
    ORDER BY id_produkt DESC
");
$produkti = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-5">
    <h1>Objave</h1>
    <p>Skupno število izdelkov: <strong><?= count($produkti) ?></strong></p>

    <div class="row g-4 mt-4">

        <?php foreach ($produkti as $p): ?>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="card h-100 shadow-sm">

                <a
        href="/podstrani/produkt.php?id=<?= $p["id_produkt"] ?>"
        class="text-decoration-none text-dark"
        style="flex-grow:1;"
    >

                    <img
                        src="/<?= htmlspecialchars($p["slika"] ?? 'slike/default-product.png') ?>"
                        class="card-img-top"
                        alt="<?= htmlspecialchars($p["naziv"]) ?>"
                    >

                    <div class="card-body text-center">
                        <h5 class="card-title">
                            <?= htmlspecialchars($p["naziv"]) ?>
                        </h5>

                        <p class="text-muted mb-2">
                            <?= $p["cena"] ? number_format($p["cena"], 2) . " €" : "Po dogovoru" ?>
                        </p>
                    </div>
        </a>

                    <div class="card-footer text-center">
                        <a
                            href="/includes/brisi_produkt.php?id=<?= $p["id_produkt"] ?>"
                            class="btn btn-sm btn-danger"
                            onclick="return confirm('Res želiš izbrisati ta izdelek?')"
                        >
                            Izbriši izdelek
                        </a>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>

<?php include "../includes/footer.php"; ?>