<?php
require_once "../includes/session.php";
include "../includes/header.php";
require_once "../includes/db.php";

if (!jeAdmin()) {
    header("Location: ../index.php");
    exit;
}

$stVsi = $pdo->query("SELECT COUNT(*) AS skupaj FROM Uporabnik");
$skupajUporabnikov = $stVsi->fetch(PDO::FETCH_ASSOC)["skupaj"];

$stAktivni = $pdo->query("SELECT COUNT(*) AS aktivni FROM Uporabnik WHERE aktiven = 1");
$aktivniUporabniki = $stAktivni->fetch(PDO::FETCH_ASSOC)["aktivni"];

$users = $pdo->query("
    SELECT 
        id_uporabnik, 
        ime, 
        priimek, 
        uporabnisko_ime, 
        TK_tip_uporabnika,
        aktiven
    FROM Uporabnik
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container my-5">

<h1>Uporabniki</h1>

<p>
    Skupno število uporabnikov: 
    <strong><?= $skupajUporabnikov ?></strong><br>
    Število aktivnih uporabnikov: 
    <strong><?= $aktivniUporabniki ?></strong>
</p>


<table class="table table-dark table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Ime</th>
            <th>E-pošta</th>
            <th>Tip</th>
            <th>Status</th>
            <th>Akcije</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= $u["id_uporabnik"] ?></td>
            <td><?= htmlspecialchars($u["ime"] . " " . $u["priimek"]) ?></td>
            <td><?= htmlspecialchars($u["uporabnisko_ime"]) ?></td>
            <td><?= $u["TK_tip_uporabnika"] == 3 ? "Admin" : "Uporabnik" ?></td>
            <td><?= $u["aktiven"] ? "Aktiven" : "Deaktiviran" ?></td>
            <td>
                <?php if ($u["TK_tip_uporabnika"] != 3): ?>
                    <a href="/includes/nastavi_admin.php?id=<?= $u["id_uporabnik"] ?>" class="btn btn-sm btn-warning">
                        Naredi admina
                    </a>
                <?php endif; ?>

                <?php if ($u["aktiven"]): ?>
                  <a href="/includes/brisi_uporabnika.php?id=<?= $u["id_uporabnik"] ?>&akcija=deaktiviraj"
                    class="btn btn-sm btn-danger"
                    onclick="return confirm('Res deaktiviram uporabnika?')">
                    Deaktiviraj
                  </a>
              <?php else: ?>
                  <a href="/includes/brisi_uporabnika.php?id=<?= $u["id_uporabnik"] ?>&akcija=aktiviraj"
                    class="btn btn-sm btn-success"
                    onclick="return confirm('Res aktiviram uporabnika?')">
                    Aktiviraj
                  </a>
              <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
              </div>

<?php include "../includes/footer.php"; ?>