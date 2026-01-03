<?php
require_once "../includes/session.php";
include "../includes/header.php";
require_once "../includes/db.php";

// Parametri GET
$tip = $_GET['tip'] ?? 'vse'; // oblacila, obutev, dodatki ali vse
$podkategorija_id = $_GET['podkategorija'] ?? null;

// Pridobimo nadkategorije
$nadkategorije_stmt = $pdo->query("SELECT id_nadkategorija_produktov, naziv FROM Nadkategorija_produktov");
$nadkategorije = $nadkategorije_stmt->fetchAll(PDO::FETCH_ASSOC);

// Pridobimo kategorije in jih razvrstimo po nadkategorijah
$kategorije_po_nadkategorijah = [];
$kategorije_stmt = $pdo->query("SELECT id_kategorija_produktov, naziv, tk_nadkategorija_produktov FROM Kategorija_produktov");
while ($row = $kategorije_stmt->fetch(PDO::FETCH_ASSOC)) {
    $kategorije_po_nadkategorijah[$row['tk_nadkategorija_produktov']][] = $row;
}

// --- Filtri ---
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
if (!empty($_GET['cena_od'])) {
    $where[] = "cena >= ?";
    $params[] = $_GET['cena_od'];
}
if (!empty($_GET['cena_do'])) {
    $where[] = "cena <= ?";
    $params[] = $_GET['cena_do'];
}

// Nadkategorija ali tip
if($tip != 'vse') {
    $tk_nadk = null;
    foreach($nadkategorije as $n){
        if($n['naziv'] == $tip) {
            $tk_nadk = $n['id_nadkategorija_produktov'];
            break;
        }
    }
    if($tk_nadk) {
        $where[] = "tk_kategorija_produktov IN (SELECT id_kategorija_produktov FROM Kategorija_produktov WHERE tk_nadkategorija_produktov = ?)";
        $params[] = $tk_nadk;
    }
}

// Razvrščanje
$orderby = "ORDER BY objavljeno DESC";
if(!empty($_GET['sort'])) {
    if($_GET['sort'] == 'cena_nizje') $orderby = "ORDER BY cena ASC";
    if($_GET['sort'] == 'cena_visje') $orderby = "ORDER BY cena DESC";
}

// Končna poizvedba
$sql = "SELECT id_produkt, naziv, cena, slika FROM Produkt";
if($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " $orderby";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produkti = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stevilo_rezultatov = count($produkti);
?>

<div class="container my-4">
    <h2><?php echo htmlspecialchars(strtoupper($tip)); ?></h2>

    <!-- FILTRI -->
    <form class="row g-3 my-3" method="get">
        <input type="hidden" name="tip" value="<?php echo htmlspecialchars($tip); ?>">

        <div class="col-md-3">
            <label>Ohranjenost:</label><br>
            <?php
            $ohranjenosti_stmt = $pdo->query("SELECT id_ohranjenost, ohranjenost FROM Ohranjenost");
            $ohranjenosti = $ohranjenosti_stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($ohranjenosti as $o): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="ohranjenost[]" value="<?php echo $o['id_ohranjenost']; ?>" id="ohr-<?php echo $o['id_ohranjenost']; ?>">
                    <label class="form-check-label" for="ohr-<?php echo $o['id_ohranjenost']; ?>"><?php echo htmlspecialchars($o['ohranjenost']); ?></label>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="col-md-3">
            <label>Cena (€):</label>
            <div class="d-flex gap-2">
                <input type="number" class="form-control" name="cena_od" placeholder="od" min="0">
                <input type="number" class="form-control" name="cena_do" placeholder="do" min="0">
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
            <a href="kategorija.php?tip=<?php echo htmlspecialchars($tip); ?>" class="btn btn-outline-dark">Počisti filtre</a>
        </div>
    </form>

    <hr>

    <!-- SEKCIJA KATEGORIJE / NADKATEGORIJE -->
    <?php if($tip != 'oblacila' && $tip != 'obutev' && $tip != 'dodatki'): ?>
        <section class="my-3">
            <h3>Nadkategorije in kategorije</h3>
            <?php foreach ($nadkategorije as $n): ?>
                <div class="mb-2">
                    <strong><?php echo htmlspecialchars($n['naziv']); ?>:</strong>
                    <?php if(isset($kategorije_po_nadkategorijah[$n['id_nadkategorija_produktov']])): ?>
                        <?php foreach($kategorije_po_nadkategorijah[$n['id_nadkategorija_produktov']] as $k): ?>
                            <a href="kategorija.php?tip=<?php echo $n['naziv']; ?>&nadkategorija=<?php echo $k['id_kategorija_produktov']; ?>" class="btn btn-outline-dark btn-sm ms-1 mb-1">
                                <?php echo htmlspecialchars($k['naziv']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </section>
        <hr>
    <?php elseif(!empty($podkategorija_id)): ?>
        <section class="my-3">
            <h3>Kategorije</h3>
            <?php 
            if(isset($kategorije_po_nadkategorijah[$podkategorija_id])):
                foreach($kategorije_po_nadkategorijah[$podkategorija_id] as $k): ?>
                    <a href="kategorija.php?tip=<?php echo $tip; ?>&nadkategorija=<?php echo $k['id_kategorija_produktov']; ?>" class="btn btn-outline-dark btn-sm ms-1 mb-1">
                        <?php echo htmlspecialchars($k['naziv']); ?>
                    </a>
                <?php endforeach;
            endif; ?>
        </section>
        <hr>
    <?php endif; ?>

    <!-- Število rezultatov -->
    <p>Število rezultatov: <?php echo $stevilo_rezultatov; ?></p>

    <div id="product-grid"></div>

  
    
    <script>
    window.products = <?php
        echo json_encode(
            $produkti ?? [],
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
    ?>;
    </script>

    
</div>

<?php include "../includes/footer.php"; ?>