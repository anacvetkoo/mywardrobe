<?php
require_once "../includes/session.php";
include "../includes/header.php";
require_once "../includes/db.php";

// ID uporabnika iz GET parametra
$uporabnik_id = $_GET['id'] ?? null;

if(!$uporabnik_id){
    echo "<p>Uporabnik ni določen.</p>";
    include "../includes/footer.php";
    exit;
}

// Podatki uporabnika
$uporabnik_stmt = $pdo->prepare("SELECT ime, priimek, uporabnisko_ime, slika FROM Uporabnik WHERE id_uporabnik = ?");
$uporabnik_stmt->execute([$uporabnik_id]);
$uporabnik = $uporabnik_stmt->fetch(PDO::FETCH_ASSOC);

if(!$uporabnik){
    echo "<p>Uporabnik ne obstaja.</p>";
    include "../includes/footer.php";
    exit;
}

// Produkti uporabnika (Moji oglasi)
$moji_oglasi_stmt = $pdo->prepare("SELECT id_produkt, naziv, cena, slika FROM Produkt WHERE tk_uporabnik = ?");
$moji_oglasi_stmt->execute([$uporabnik_id]);
$moji_oglasi = $moji_oglasi_stmt->fetchAll(PDO::FETCH_ASSOC);

// Wishlist (za zdaj izpišemo nekaj produktov, lahko random)
$wishlist_stmt = $pdo->query("SELECT id_produkt, naziv, cena, slika FROM Produkt LIMIT 6");
$wishlist = $wishlist_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container my-5">
    <!-- UPORABNIK -->
    <div class="d-flex align-items-center mb-4 flex-wrap">
        <img src="data:image/jpeg;base64,<?php echo base64_encode($uporabnik['slika']); ?>" class="rounded-circle me-3" width="100" height="100" alt="Profilna slika">
        <div>
            <h3 class="mb-1"><?php echo htmlspecialchars($uporabnik['ime'] . ' ' . $uporabnik['priimek']); ?></h3>
            <p class="mb-0"><i class="bi bi-person-fill me-1"></i><?php echo htmlspecialchars($uporabnik['uporabnisko_ime']); ?></p>
            <p class="mb-0"><i class="bi bi-geo-alt-fill me-1"></i>Lokacija</p>
        </div>
        <div class="ms-auto">
            <button class="btn btn-dark">Uredi profil</button>
            <a href="/includes/odjava.php" class="btn btn-outline-danger ms-2">Odjava</a>
        </div>
    </div>

    <!-- MOJI OGLASI in WISHLIST -->
    <div class="row">
        <!-- Levi: Moji oglasi -->
        <div class="col-lg-6 mb-4">
            <h4>Moji oglasi</h4>
            <div class="row g-3">
                <?php foreach($moji_oglasi as $p): ?>
                <div class="col-6 col-md-6 col-lg-6">
                    <div class="produkt-kartica bg-white p-2 rounded position-relative">
                        <div class="slika-ovoj position-relative">
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($p['slika']); ?>" class="img-fluid rounded" alt="">
                        </div>
                        <h5 class="mt-2"><?php echo htmlspecialchars($p['naziv']); ?></h5>
                        <p class="cena"><?php echo $p['cena'] ? number_format($p['cena'],2)." €" : "po dogovoru"; ?></p>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-dark" title="Uredi">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-dark" title="Izbriši">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Desni: Wishlist -->
        <div class="col-lg-6 mb-4">
            <h4>Wishlist</h4>
            <?php
            foreach ($wishlist as &$p) {
                if (!empty($p['slika'])) {
                    $p['slika'] = base64_encode($p['slika']);
                } else {
                    $p['slika'] = null;
                }
            }
            unset($p);
            ?>
            <div id="product-grid" data-layout="wishlist"></div>
        </div>
    </div>
</div>
<script>
window.products = <?php
    echo json_encode(
        $wishlist ?? [],
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    );
?>;
</script>

<?php include "../includes/footer.php"; ?>