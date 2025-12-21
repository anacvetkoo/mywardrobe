<?php
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

    <div class="row g-4">
        <?php foreach ($produkti as $p): ?>

        <div class="col-6 col-md-3 col-lg-2">

            <!-- Celotna kartica je link -->
            <a href="podstrani/produkt.php?id=<?php echo $p['id_produkt']; ?>" 
               class="text-decoration-none text-dark">

                <div class="card h-100 shadow-sm produkt-kartica">

                    <div class="position-relative slika-ovoj">
                        <img 
    src="data:image/jpeg;base64,<?php echo base64_encode($p['slika']); ?>" 
    class="card-img-top"
    alt="<?php echo htmlspecialchars($p['naziv']); ?>"
>

                        <!-- Wishlist ikona -->
                        <button class="btn btn-light position-absolute top-0 end-0 m-2 p-1 rounded-circle wishlist-btn">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>

                    <div class="card-body text-center">
                        <h5 class="card-title" style="font-size: 1rem;">
                            <?php echo htmlspecialchars($p['naziv']); ?>
                        </h5>

                        <p class="cena mb-0 text-muted">
                            <?php 
                                echo ($p['cena'] === null || $p['cena'] === "")
                                    ? "Po dogovoru"
                                    : number_format($p['cena'], 2) . " €";
                            ?>
                        </p>
                    </div>

                </div>

            </a>

        </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
        <a href="kategorija.php" class="btn btn-secondary">Pokaži več</a>
    </div>
</section>

<?php
  include "includes/footer.php";
?>
</body>
</html>