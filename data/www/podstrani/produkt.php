<?php
require_once "../includes/session.php";
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

$jeVWishlistu = false;

if (isset($_SESSION["uporabnik_id"])) {
    $w = $pdo->prepare("SELECT 1 FROM Wishlist WHERE TK_uporabnik = ? AND TK_produkt = ?");
    $w->execute([$_SESSION["uporabnik_id"], $produkt["id_produkt"]]);
    $jeVWishlistu = (bool)$w->fetchColumn();
}

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
                <img src="/<?php echo htmlspecialchars($produkt['slika'] ?? 'slike/default-product.png'); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($produkt['naziv']); ?>">
            
            </div>
        </div>

        <!-- DESNA STRAN: PODATKI -->
        <div class="col-12 col-md-6">
    <div class="produkt-info">
        <h2 class="d-flex align-items-center justify-content-between">
            <span><?php echo htmlspecialchars($produkt['naziv']); ?></span>

            <button
                type="button"
                id="wishlistBtn"
                class="btn btn-outline-dark"
                aria-label="Dodaj na wishlist"
                <?php if (!isset($_SESSION["uporabnik_id"])): ?>
                    data-not-logged="1"
                <?php endif; ?>
            >
                <i id="wishlistIcon" class="bi <?php echo $jeVWishlistu ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
            </button>
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


<script>
document.getElementById("wishlistBtn")?.addEventListener("click", async function () {

    // če ni prijavljen -> samo alert (brez redirecta)
    if (this.dataset.notLogged === "1") {
        alert("Za wishlist morate biti prijavljeni.");
        return;
    }

    const icon = document.getElementById("wishlistIcon");
    const produktId = <?php echo (int)$produkt["id_produkt"]; ?>;

    try {
        const res = await fetch("/includes/wishlist_toggle.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ produkt_id: produktId })
        });

        const data = await res.json();

        if (!data.success) {
            alert(data.message || "Napaka pri wishlistu.");
            return;
        }

        if (data.action === "added") {
            icon.classList.remove("bi-heart");
            icon.classList.add("bi-heart-fill", "text-danger");
        } else if (data.action === "removed") {
            icon.classList.remove("bi-heart-fill", "text-danger");
            icon.classList.add("bi-heart");
        }

    } catch (e) {
        console.error(e);
        alert("Napaka pri povezavi (wishlist).");
    }
});
</script>


<?php include "../includes/footer.php"; ?>