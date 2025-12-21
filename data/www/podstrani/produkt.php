<?php
include "../includes/header.php";
require_once "../includes/db.php";

// Preverimo, ali je poslan ID produkta
$id_produkt = $_GET['id'] ?? null;

if(!$id_produkt) {
    echo "<p>Produkt ni izbran.</p>";
    include "../includes/footer.php";
    exit;
}

// Pridobimo podatke o produktu
$stmt = $pdo->prepare("
    SELECT p.id_produkt, p.naziv, p.cena, p.slika, p.opis, o.ohranjenost 
    FROM Produkt p
    LEFT JOIN Ohranjenost o ON p.tk_ohranjenost = o.id_ohranjenost
    WHERE p.id_produkt = ?
");
$stmt->execute([$id_produkt]);
$produkt = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$produkt) {
    echo "<p>Produkt ne obstaja.</p>";
    include "../includes/footer.php";
    exit;
}
?>

<div class="container my-5">
    <div class="row g-4">
        <!-- LEVA STRAN: SLIKA -->
        <div class="col-12 col-md-6">
            <div class="produkt-slika text-center">
                <img src="data:image/jpeg;base64,<?php echo base64_encode($produkt['slika']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($produkt['naziv']); ?>">
            
            </div>
        </div>

        <!-- DESNA STRAN: PODATKI -->
        <div class="col-12 col-md-6">
    <div class="produkt-info">
        <h2 class="d-flex align-items-center justify-content-between">
            <?php echo htmlspecialchars($produkt['naziv']); ?>
          
        </h2>

        <p><strong>Ohranjenost:</strong> <?php echo htmlspecialchars($produkt['ohranjenost']); ?></p>
        <p><strong>Cena:</strong> <?php echo $produkt['cena'] ? number_format($produkt['cena'],2)." €" : "po dogovoru"; ?></p>
        <p><?php echo nl2br(htmlspecialchars($produkt['opis'])); ?></p>

        <div class="d-flex gap-2 mt-3">
            <button class="btn btn-dark">Kupi zdaj</button>
            <button class="btn btn-outline-dark">Kontaktiraj prodajalca</button>
        </div>
    </div>
</div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>