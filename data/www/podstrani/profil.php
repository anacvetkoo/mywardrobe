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
    $lat = isset($_POST["lokacija_lat"]) && $_POST["lokacija_lat"] !== ''
        ? (float)$_POST["lokacija_lat"]
        : null;

    $lng = isset($_POST["lokacija_lng"]) && $_POST["lokacija_lng"] !== ''
        ? (float)$_POST["lokacija_lng"]
        : null;

    // osnovni update
    $sql = "UPDATE Uporabnik SET ime = ?, priimek = ?";
    $params = [$ime, $priimek];
    if ($lat !== null && $lng !== null) {
        $sql .= ", lokacija_lat = ?, lokacija_lng = ?";
        $params[] = $lat;
        $params[] = $lng;
    }

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
$uporabnik_stmt = $pdo->prepare("
    SELECT ime, priimek, uporabnisko_ime, slika, lokacija_lat, lokacija_lng
    FROM Uporabnik WHERE id_uporabnik = ?
");
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
            
        </div>

        <div class="ms-auto">
            <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#urediProfilModal">
                Uredi profil
            </button>
            <a href="/includes/odjava.php" class="btn btn-outline-danger ms-2">Odjava</a>
        </div>
            


            <?php if (!empty($uporabnik['lokacija_lat']) && !empty($uporabnik['lokacija_lng'])): ?>
                <div id="map" style="height:300px; width:100%; border-radius:8px; margin-top:10px"></div>
            <?php else: ?>
            <?php endif; ?>
        
        
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

        <hr>

        <?php
$imaLokacijo = !empty($uporabnik['lokacija_lat']) && !empty($uporabnik['lokacija_lng']);
?>

<div class="mb-3">
    <label class="form-label">Natančna lokacija</label>

    <button
        type="button"
        class="btn btn-outline-dark w-100"
        id="btn-lokacija"
    >
        <?= $imaLokacijo ? 'Posodobi lokacijo' : 'Uporabi mojo lokacijo' ?>
    </button>

    <?php if ($imaLokacijo): ?>
        <small class="form-text text-success d-block mt-1">
            Lokacija je že nastavljena. Z gumbom jo lahko posodobiš.
        </small>
    <?php else: ?>
        <small class="form-text text-muted">
            Brskalnik bo zahteval dovoljenje za dostop do lokacije.
        </small>
    <?php endif; ?>

    <!-- skrita polja za koordinate -->
    <input type="hidden" name="lokacija_lat" id="lokacija_lat">
    <input type="hidden" name="lokacija_lng" id="lokacija_lng">

    <!-- status -->
    <div id="lokacija-status" class="mt-2"></div>
</div>


      </div>

      <div class="modal-footer">
        <button type="submit" name="shrani_profil" class="btn btn-dark">Shrani</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Prekliči</button>
      </div>

    </form>
  </div>
</div>

<script>
document.getElementById("btn-lokacija")?.addEventListener("click", function () {

    const status = document.getElementById("lokacija-status");
    status.textContent = "Pridobivam lokacijo…";

    if (!navigator.geolocation) {
        status.textContent = "Geolokacija ni podprta v tem brskalniku.";
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function (position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            document.getElementById("lokacija_lat").value = lat;
            document.getElementById("lokacija_lng").value = lng;

            status.innerHTML =
                "Lokacija uspešno pridobljena.<br>" +
                "<small>Lat: " + lat.toFixed(6) + ", Lng: " + lng.toFixed(6) + "</small>";
        },
        function (error) {
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    status.textContent = "Dostop do lokacije je bil zavrnjen.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    status.textContent = "Lokacija ni na voljo.";
                    break;
                case error.TIMEOUT:
                    status.textContent = "Časovna omejitev pri pridobivanju lokacije.";
                    break;
                default:
                    status.textContent = "Napaka pri pridobivanju lokacije.";
            }
        }
    );
});
</script>

<?php if (!empty($uporabnik['lokacija_lat']) && !empty($uporabnik['lokacija_lng'])): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const lat = <?= (float)$uporabnik['lokacija_lat'] ?>;
    const lng = <?= (float)$uporabnik['lokacija_lng'] ?>;

    const map = L.map('map').setView([lat, lng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.marker([lat, lng])
        .addTo(map)
        .bindPopup("Tvoja lokacija")
        .openPopup();
});
</script>
<?php endif; ?>
<?php include "../includes/footer.php"; ?>