<?php
require_once "../includes/session.php";
require_once "../includes/db.php";

$iskalni_niz = trim($_GET["q"] ?? "");

$produkti = [];
$stevilo_rezultatov = 0;

if ($iskalni_niz !== "") {
    // LIKE iskanje po nazivu in opisu
    $like = "%" . $iskalni_niz . "%";

    $stmt = $pdo->prepare("
        SELECT id_produkt, naziv, cena, slika
        FROM Produkt
        WHERE naziv LIKE ?
           OR opis LIKE ?
        ORDER BY objavljeno DESC
    ");
    $stmt->execute([$like, $like]);
    $produkti = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stevilo_rezultatov = count($produkti);
}

include "../includes/header.php";
?>

<div class="container my-4">
    <h2>ISKANJE</h2>

    <?php if ($iskalni_niz === ""): ?>
        <div class="alert alert-warning mt-3">
            Vnesite iskalni niz.
        </div>
    <?php else: ?>
        <p class="mt-2">
            Rezultati za: <strong><?= htmlspecialchars($iskalni_niz) ?></strong><br>
            Število rezultatov: <strong><?= $stevilo_rezultatov ?></strong>
        </p>

        <?php if ($stevilo_rezultatov === 0): ?>
            <div class="alert alert-info">
                Ni najdenih izdelkov za iskani niz.
            </div>
        <?php endif; ?>

        <div id="product-grid"></div>

        <script>
        window.products = <?php
            echo json_encode(
                $produkti,
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            );
        ?>;
        </script>
    <?php endif; ?>
</div>

<?php include "../includes/footer.php"; ?>
