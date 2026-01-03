<?php
require_once "../includes/session.php";
require_once "../includes/db.php";

/*
PARAMETRI:
- nadkategorija = naziv nadkategorije (oblacila, obutev, dodatki)
- kategorija    = id_kategorija_produktov
*/

// GET parametri
$nadkategorija_naziv = $_GET['nadkategorija'] ?? null;
$kategorija_id = $_GET['kategorija'] ?? null;

// --- PRIDOBIMO NADKATEGORIJE ---
$nadkategorije_stmt = $pdo->query("
    SELECT id_nadkategorija_produktov, naziv
    FROM Nadkategorija_produktov
");
$nadkategorije = $nadkategorije_stmt->fetchAll(PDO::FETCH_ASSOC);

// --- PRIDOBIMO KATEGORIJE PO NADKATEGORIJAH ---
$kategorije_po_nadkategorijah = [];
$kategorije_stmt = $pdo->query("
    SELECT id_kategorija_produktov, naziv, tk_nadkategorija_produktov
    FROM Kategorija_produktov
");
while ($row = $kategorije_stmt->fetch(PDO::FETCH_ASSOC)) {
    $kategorije_po_nadkategorijah[$row['tk_nadkategorija_produktov']][] = $row;
}

// --- FILTRI ---
$where = [];
$params = [];

// Ohranjenost
if (!empty($_GET['ohranjenost'])) {
    $ohranjenosti = $_GET['ohranjenost'];
    $placeholders = implode(',', array_fill(0, count($ohranjenosti), '?'));
    $where[] = "tk_ohranjenost IN ($placeholders)";
    $params = array_merge($params, $ohranjenosti);
}

// Cena
$cena_od = $_GET['cena_od'] ?? null;
$cena_do = $_GET['cena_do'] ?? null;
$po_dogovoru = isset($_GET['po_dogovoru']);

$cena_and = [];
$cena_or = [];

// OD
if ($cena_od !== null && $cena_od !== '') {
    $cena_and[] = "cena >= ?";
    $params[] = $cena_od;
}

// DO
if ($cena_do !== null && $cena_do !== '') {
    $cena_and[] = "cena <= ?";
    $params[] = $cena_do;
}

// če imamo OD ali DO
if ($cena_and) {
    $cena_or[] = '(' . implode(' AND ', $cena_and) . ')';
}

// PO DOGOVORU
if ($po_dogovoru) {
    $cena_or[] = "cena IS NULL";
}

// končni pogoj
if ($cena_or) {
    $where[] = '(' . implode(' OR ', $cena_or) . ')';
}


// --- NADKATEGORIJA / KATEGORIJA ---
if ($nadkategorija_naziv !== null) {

    // pridobimo ID nadkategorije iz naziva
    $stmt = $pdo->prepare("
        SELECT id_nadkategorija_produktov
        FROM Nadkategorija_produktov
        WHERE LOWER(naziv) = LOWER(?)
        LIMIT 1
    ");
    $stmt->execute([$nadkategorija_naziv]);
    $nadk = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($nadk) {

        // če je izbrana konkretna kategorija
        if (!empty($kategorija_id)) {
            $where[] = "tk_kategorija_produktov = ?";
            $params[] = $kategorija_id;
        } else {
            // sicer vsi produkti te nadkategorije
            $where[] = "
                tk_kategorija_produktov IN (
                    SELECT id_kategorija_produktov
                    FROM Kategorija_produktov
                    WHERE tk_nadkategorija_produktov = ?
                )
            ";
            $params[] = $nadk['id_nadkategorija_produktov'];
        }
    }
}

// --- RAZVRŠČANJE (privzeto: najnovejši) ---
$orderby = "ORDER BY objavljeno DESC";

if (!empty($_GET['sort'])) {
    if ($_GET['sort'] === 'cena_nizje') {
        // NULL (po dogovoru) na konec
        $orderby = "ORDER BY cena IS NULL, cena ASC";
    }
    if ($_GET['sort'] === 'cena_visje') {
        $orderby = "ORDER BY cena IS NULL, cena DESC";
    }
}

// --- KONČNA POIZVEDBA ---
$sql = "SELECT id_produkt, naziv, cena, slika FROM Produkt";
if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}
$sql .= " $orderby";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produkti = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stevilo_rezultatov = count($produkti);

include "../includes/header.php";
?>

<div class="container my-4">

    <h2>
    <?php
    if ($kategorija_id !== null) {
        // naslov = naziv kategorije
        $stmt = $pdo->prepare("
            SELECT naziv
            FROM Kategorija_produktov
            WHERE id_kategorija_produktov = ?
            LIMIT 1
        ");
        $stmt->execute([$kategorija_id]);
        $kat = $stmt->fetch(PDO::FETCH_ASSOC);

        echo $kat
            ? strtoupper(htmlspecialchars($kat['naziv']))
            : 'IZDELKI';
    }
    elseif ($nadkategorija_naziv !== null) {
        echo strtoupper(htmlspecialchars($nadkategorija_naziv));
    }
    else {
        echo 'VSI IZDELKI';
    }
    ?>
    </h2>


    <!-- FILTRI -->
    <form class="row g-3 my-3" method="get">
        <?php if ($nadkategorija_naziv): ?>
            <input type="hidden" name="nadkategorija" value="<?= htmlspecialchars($nadkategorija_naziv) ?>">
        <?php endif; ?>
        <?php if ($kategorija_id): ?>
            <input type="hidden" name="kategorija" value="<?= htmlspecialchars($kategorija_id) ?>">
        <?php endif; ?>

        <div class="col-md-3">
            <label>Ohranjenost:</label><br>
            <?php
            $ohranjenosti_stmt = $pdo->query("SELECT id_ohranjenost, ohranjenost FROM Ohranjenost");
            foreach ($ohranjenosti_stmt as $o):
            ?>
                <div class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="ohranjenost[]"
                        value="<?= $o['id_ohranjenost'] ?>"
                        <?= isset($_GET['ohranjenost']) && in_array($o['id_ohranjenost'], $_GET['ohranjenost'])
                            ? 'checked'
                            : '' ?>
                    >
                    <label class="form-check-label">
                        <?= htmlspecialchars($o['ohranjenost']) ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="col-md-3">
            <label>Cena (€):</label>
            <div class="d-flex gap-2">
                <input type="number" class="form-control" name="cena_od" placeholder="od">
                <input type="number" class="form-control" name="cena_do" placeholder="do">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check mt-4">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="po_dogovoru"
                    value="1"
                    id="po-dogovoru"
                    <?= isset($_GET['po_dogovoru']) ? 'checked' : '' ?>
                >
                <label class="form-check-label" for="po-dogovoru">
                    Po dogovoru
                </label>
            </div>
        </div>

        <div class="col-md-3">
            <label>Razvrsti:</label>
            <select class="form-select" name="sort">
                <option value="">Najprej najnovejše</option>
                <option value="cena_visje">Cena: od višje do nižje</option>
                <option value="cena_nizje">Cena: od nižje do višje</option>
            </select>
        </div>

        <div class="col-md-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-dark">Filtriraj</button>
            <a href="kategorija.php<?= $nadkategorija_naziv ? '?nadkategorija=' . urlencode($nadkategorija_naziv) : '' ?>"
            class="btn btn-outline-dark">
                Počisti filtre
            </a>
        </div>
    </form>

    <hr>

    <!-- KATEGORIJE -->
    <?php if ($kategorija_id === null): ?>
        <section class="my-3">
    <h3>Kategorije</h3>

    <?php foreach ($nadkategorije as $n): ?>

        <?php
        // če je izbrana nadkategorija, preskočimo ostale
        if ($nadkategorija_naziv !== null && strcasecmp($n['naziv'], $nadkategorija_naziv) !== 0) {
            continue;
        }
        ?>

        <div class="mb-2">
            <?php if ($nadkategorija_naziv === null): ?>
                <strong><?= htmlspecialchars($n['naziv']) ?>:</strong>
            <?php endif; ?>

            <?php if (isset($kategorije_po_nadkategorijah[$n['id_nadkategorija_produktov']])): ?>
                <?php foreach ($kategorije_po_nadkategorijah[$n['id_nadkategorija_produktov']] as $k): ?>
                    <a
                        href="kategorija.php?nadkategorija=<?= urlencode($n['naziv']) ?>&kategorija=<?= $k['id_kategorija_produktov'] ?>"
                        class="btn btn-outline-dark btn-sm ms-1 mb-1"
                    >
                        <?= htmlspecialchars($k['naziv']) ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    <?php endforeach; ?>
</section>
<hr>
    <?php endif; ?>

    <p>Število rezultatov: <strong><?= $stevilo_rezultatov ?></strong></p>

    <div id="product-grid"></div>

    <script>
        window.products = <?= json_encode(
            $produkti,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        ); ?>;
    </script>

</div>

<?php include "../includes/footer.php"; ?>
