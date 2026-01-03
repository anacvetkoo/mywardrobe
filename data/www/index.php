<?php
    require_once "includes/session.php";
    include "includes/header.php";
    require_once "includes/db.php";

    // Query: 12 najnovejših produktov
    $stmt = $pdo->prepare("
        SELECT id_produkt, naziv, cena, slika 
        FROM Produkt 
        ORDER BY objavljeno DESC 
        LIMIT 12
    ");
    $stmt->execute();
    $produkti = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="hero">
    <div class="hero-content">
        <h1>Želite prevetriti svojo garderobo?</h1>
        <a href="podstrani/pordaj.php" class="btn">Prodaj zdaj</a>
    </div>
</section>

<section class="novo container my-5">
    <h2 class="mb-4">NOVO</h2>

    <div id="product-grid"></div>
    
    <script>
        const products = <?php echo json_encode($produkti,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    </script>

    <div class="text-center mt-4">
        <a href="kategorija.php" class="btn btn-secondary">Pokaži več</a>
    </div>
</section>

<?php
  include "includes/footer.php";
?>
</body>
</html>