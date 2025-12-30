<?php
require_once "../includes/session.php";
include "../includes/header.php";
require_once "../includes/db.php";

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

    <form action="prodaj_submit.php" method="post" enctype="multipart/form-data" class="row g-3">
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