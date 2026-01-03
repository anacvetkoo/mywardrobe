<?php
require_once "../includes/session.php";

require_once "../includes/db.php";

$napaka = "";

// OBJAVA IZDELKA (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 1) če ni prijavljen -> samo izpiši napako (brez redirecta!)
    if (!isset($_SESSION["uporabnik_id"])) {
        $napaka = "Za objavo izdelkov morate biti prijavljeni.";
    } else {

        // 2) preberi podatke
        $naziv = trim($_POST["naziv"] ?? "");
        $opis = trim($_POST["opis"] ?? "");
        $cena_raw = trim($_POST["cena"] ?? "");
        $kategorija = $_POST["kategorija"] ?? null;
        $ohranjenost = $_POST["ohranjenost"] ?? null;

        // cena je lahko null
        $cena = ($cena_raw === "") ? null : (float)$cena_raw;

        // osnovna validacija
        if ($naziv === "" || $opis === "" || empty($kategorija) || empty($ohranjenost)) {
            $napaka = "Prosim izpolnite vsa obvezna polja.";
        } elseif (!isset($_FILES["slika"]) || $_FILES["slika"]["error"] !== UPLOAD_ERR_OK) {
            $napaka = "Prosim dodajte fotografijo izdelka.";
        } else {

            // 3) upload slike
            $ext = strtolower(pathinfo($_FILES["slika"]["name"], PATHINFO_EXTENSION));
            $dovoljeni = ["jpg", "jpeg", "png", "webp"];

            if (!in_array($ext, $dovoljeni)) {
                $napaka = "Slika mora biti JPG, PNG ali WEBP.";
            } else {

                $uporabnik_id = (int)$_SESSION["uporabnik_id"];

                // ime datoteke
                $imeDatoteke = "product_" . $uporabnik_id . "_" . time() . "." . $ext;

                // fizična pot (prodaj.php je v /podstrani/ -> zato ../uploads/...)
                $uploadDir = "../uploads/products/";
                $targetPath = $uploadDir . $imeDatoteke;

                if (!move_uploaded_file($_FILES["slika"]["tmp_name"], $targetPath)) {
                    $napaka = "Napaka pri shranjevanju slike na strežnik.";
                } else {

                    // pot v bazi (relativna od www/)
                    $slikaPot = "uploads/products/" . $imeDatoteke;

                    // 4) insert v bazo po tvoji strukturi
                    // id_produkt auto, objavljeno timestamp, TK_transakcija NULL
                    $stmt = $pdo->prepare("
                        INSERT INTO Produkt
                        (naziv, opis, cena, objavljeno, TK_uporabnik, TK_kategorija_produktov, TK_transakcija, TK_ohranjenost, slika)
                        VALUES (?, ?, ?, NOW(), ?, ?, NULL, ?, ?)
                    ");

                    $stmt->execute([
                        $naziv,
                        $opis,
                        $cena,
                        $uporabnik_id,
                        $kategorija,
                        $ohranjenost,
                        $slikaPot
                    ]);

                    // 5) redirect na profil
                    header("Location: profil.php?id=" . $uporabnik_id);
                    exit;
                }
            }
        }
    }
}

include "../includes/header.php";

// Pridobimo nadkategorije
$nadkategorije_stmt = $pdo->query("SELECT id_nadkategorija_produktov, naziv FROM Nadkategorija_produktov");
$nadkategorije = $nadkategorije_stmt->fetchAll(PDO::FETCH_ASSOC);

// Pridobimo vse kategorije
$kategorije_stmt = $pdo->query("SELECT id_kategorija_produktov, naziv, tk_nadkategorija_produktov FROM Kategorija_produktov");
$kategorije = $kategorije_stmt->fetchAll(PDO::FETCH_ASSOC);

// Pridobimo ohranjenosti
$ohranjenosti_stmt = $pdo->query("SELECT id_ohranjenost, ohranjenost FROM Ohranjenost");
$ohranjenosti = $ohranjenosti_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container my-5">
    <h1 class="mb-4">PRODAJ ZDAJ</h1>

    <?php if (!empty($napaka)): ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($napaka); ?>
    </div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-12">
            <label for="naziv" class="form-label">Naziv izdelka</label>
            <input type="text" class="form-control" id="naziv" name="naziv" required>
        </div>

        <div class="col-12">
            <label for="opis" class="form-label">Opis</label>
            <textarea class="form-control" id="opis" name="opis" rows="4" required></textarea>
        </div>

        <div class="col-12">
            <label for="slika" class="form-label">Fotografija</label>
            <input type="file" class="form-control" id="slika" name="slika" accept="image/*" required>
        </div>

        <div class="col-md-6">
            <label for="nadkategorija" class="form-label">Nadkategorija</label>
            <select class="form-select" id="nadkategorija" name="nadkategorija" required>
                <option value="" disabled selected>Izberi nadkategorijo</option>
                <?php foreach($nadkategorije as $n): ?>
                    <option value="<?php echo $n['id_nadkategorija_produktov']; ?>"><?php echo htmlspecialchars($n['naziv']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label for="kategorija" class="form-label">Kategorija</label>
            <select class="form-select" id="kategorija" name="kategorija" required>
                <option value="" disabled selected>Najprej izberi nadkategorijo</option>
                <!-- Tu se bodo dinamično prikazale kategorije preko JS -->
            </select>
        </div>

        <div class="col-md-6">
            <label for="cena" class="form-label">Cena (€)</label>
            <input type="number" class="form-control" id="cena" name="cena" min="0" step="0.01" required>
        </div>

        <div class="col-md-6">
            <label for="ohranjenost" class="form-label">Ohranjenost</label>
            <select class="form-select" id="ohranjenost" name="ohranjenost" required>
                <option value="" disabled selected>Izberi ohranjenost</option>
                <?php foreach($ohranjenosti as $o): ?>
                    <option value="<?php echo $o['id_ohranjenost']; ?>"><?php echo htmlspecialchars($o['ohranjenost']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-dark mt-3">Objavi</button>
        </div>
    </form>
</div>

<script>
// Pretvorimo vse kategorije v JS objekt
const vseKategorije = <?php echo json_encode($kategorije); ?>;
const nadkategorijaSelect = document.getElementById('nadkategorija');
const kategorijaSelect = document.getElementById('kategorija');

nadkategorijaSelect.addEventListener('change', function() {
    const izbranaId = parseInt(this.value);
    kategorijaSelect.innerHTML = '<option value="" disabled selected>Izberi kategorijo</option>';

    vseKategorije.forEach(k => {
        if(k.tk_nadkategorija_produktov == izbranaId){
            const option = document.createElement('option');
            option.value = k.id_kategorija_produktov;
            option.textContent = k.naziv;
            kategorijaSelect.appendChild(option);
        }
    });
});
</script>

<?php include "../includes/footer.php"; ?>