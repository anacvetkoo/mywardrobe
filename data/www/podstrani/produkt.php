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
    <div id="kontaktGlobalSuccess"
        class="alert alert-success text-center d-none">
        Kontaktiranje prodajalca je bilo uspešno ✔
    </div>
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
            <!-- GUMB -->
            <button class="btn btn-outline-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#kontaktModal">
                Kontaktiraj prodajalca
            </button>

            <!-- MODAL -->
            <div class="modal fade" id="kontaktModal" tabindex="-1" style="color:black;">
            <div class="modal-dialog">
                <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Kontaktiraj prodajalca</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <textarea id="kontaktSporocilo"
                            class="form-control"
                            rows="5"
                            placeholder="Vpiši sporočilo..."></textarea>

                    <div id="kontaktError" class="alert alert-danger mt-2 d-none">
                        Sporočilo je obvezno.
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Prekliči</button>
                    <button class="btn btn-primary" onclick="posljiKontakt()">
                        Pošlji
                    </button>
                </div>

                </div>
            </div>
            </div>

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

<script>
function posljiKontakt() {
    const msg = document.getElementById("kontaktSporocilo").value.trim();
    const errorBox = document.getElementById("kontaktError");

    // reset opozorila
    errorBox.classList.add("d-none");

    //validacija – prazno sporočilo
    if (msg === "") {
        errorBox.classList.remove("d-none");
        return;
    }

    fetch("/includes/poslji_sporocilo_produkt.php", {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            produkt_id: <?= (int)$produkt['id_produkt'] ?>,
            sporocilo: msg
        })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            errorBox.textContent = data.error || "Prišlo je do napake.";
            errorBox.classList.remove("d-none");
            return;
        }

        // uspešno → zapri modal
        const modalEl = document.getElementById("kontaktModal");
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();

        // ⬇⬇⬇ DODAJ TO TAKOJ ZA modal.hide()
        setTimeout(() => {
            document.body.classList.remove("modal-open");

            document.querySelectorAll(".modal-backdrop").forEach(el => el.remove());
        }, 50);


        // počisti textarea
        document.getElementById("kontaktSporocilo").value = "";

        // pokaži globalno obvestilo
        const successBox = document.getElementById("kontaktGlobalSuccess");
        successBox.classList.remove("d-none");

        // skrij obvestilo po 5s
        setTimeout(() => {
            successBox.classList.add("d-none");
        }, 5000);
    })
    .catch(() => {
        errorBox.textContent = "Napaka pri povezavi s strežnikom.";
        errorBox.classList.remove("d-none");
    });
}
</script>





<?php include "../includes/footer.php"; ?>