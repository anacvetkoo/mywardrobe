<?php
require_once "../includes/session.php";

require_once "../includes/db.php";

// SHRANJEVANJE SPREMEMB PROFILA
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["shrani_profil"])) {

    $id = $_GET["id"] ?? null;

        if (!$id) {
            die("Neveljaven uporabnik.");
        }
    $ime = trim($_POST["ime"]);
    $priimek = trim($_POST["priimek"]);

    // osnovni update
    $sql = "UPDATE Uporabnik SET ime = ?, priimek = ?";
    $params = [$ime, $priimek];

    // nova slika (neobvezno)
    if (isset($_FILES["nova_slika"]) && $_FILES["nova_slika"]["error"] === UPLOAD_ERR_OK) {

        $ext = strtolower(pathinfo($_FILES["nova_slika"]["name"], PATHINFO_EXTENSION));
        $dovoljeni = ["jpg", "jpeg", "png", "webp"];

        if (in_array($ext, $dovoljeni)) {

            $imeDat = "user_" . $id . "_" . time() . "." . $ext;
            $uploadDir = "../uploads/users/";
            move_uploaded_file($_FILES["nova_slika"]["tmp_name"], $uploadDir . $imeDat);

            $sql .= ", slika = ?";
            $params[] = "uploads/users/" . $imeDat;
        }
    }

    $sql .= " WHERE id_uporabnik = ?";
    $params[] = $id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    header("Location: profil.php?id=" . $id);
    exit;
}

include "../includes/header.php";

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
        <img src="/<?php echo htmlspecialchars($uporabnik['slika'] ?? '../slike/default-user.png'); ?>" class="rounded-circle me-3" width="100" height="100" alt="Profilna slika">
        <div>
            <h3 class="mb-1"><?php echo htmlspecialchars($uporabnik['ime'] . ' ' . $uporabnik['priimek']); ?></h3>
            <p class="mb-0"><i class="bi bi-person-fill me-1"></i><?php echo htmlspecialchars($uporabnik['uporabnisko_ime']); ?></p>
            <p class="mb-0"><i class="bi bi-geo-alt-fill me-1"></i>Lokacija</p>
        </div>
        <div class="ms-auto">
            <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#urediProfilModal">
                Uredi profil
            </button>
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
                            <img src="/<?php echo htmlspecialchars($p['slika']); ?>" class="img-fluid rounded" alt="">
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

<div class="modal fade" id="urediProfilModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post" enctype="multipart/form-data" style="color:black;">

      <div class="modal-header">
        <h5 class="modal-title">Uredi profil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

    

        <div class="mb-3">
          <label class="form-label">Ime</label>
          <input type="text" name="ime" class="form-control"
                 value="<?php echo htmlspecialchars($uporabnik['ime']); ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Priimek</label>
          <input type="text" name="priimek" class="form-control"
                 value="<?php echo htmlspecialchars($uporabnik['priimek']); ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Nova profilna slika</label>
          <input type="file" name="nova_slika" class="form-control" accept="image/*">
        </div>

      </div>

      <div class="modal-footer">
        <button type="submit" name="shrani_profil" class="btn btn-dark">Shrani</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Prekliči</button>
      </div>

    </form>
  </div>
</div>

<?php include "../includes/footer.php"; ?>