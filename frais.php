<?php
session_start();
require 'config.php';

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

function normalizeMois(string $mois): ?string {
    $mois = trim($mois);
    if (preg_match('/^(\d{4})-(\d{2})$/', $mois, $matches)) {
        return $matches[1] . $matches[2];
    }
    if (preg_match('/^(\d{6})$/', $mois, $matches)) {
        return $matches[1];
    }
    return null;
}

$idVisiteur = $_SESSION['id'];
$selectedMois = normalizeMois($_GET['mois'] ?? '') ?? date('Ym');
$currentMonth = DateTime::createFromFormat('Ym', $selectedMois);
$currentMonthLabel = $currentMonth ? $currentMonth->format('F Y') : date('F Y');
$currentMonthValue = $currentMonth ? $currentMonth->format('Y-m') : date('Y-m');

$forfaitLignes = [];
$horsForfaitLignes = [];
$etatFiche = 'En attente';
$totalForfait = 0.0;
$totalHorsForfait = 0.0;
$totalGlobal = 0.0;
$error = '';

try {
    $stmt = $bdd->prepare(
        'SELECT f.idFraisForfait, ff.libelle, ff.montant, f.quantite,
                (ff.montant * f.quantite) AS total
         FROM LigneFraisForfait AS f
         JOIN FraisForfait AS ff ON ff.id = f.idFraisForfait
         WHERE f.idVisiteur = ? AND f.mois = ?'
    );
    $stmt->execute([$idVisiteur, $selectedMois]);
    $forfaitLignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($forfaitLignes as $ligne) {
        $totalForfait += (float) $ligne['total'];
    }

    $stmt = $bdd->prepare(
        'SELECT id, libelle, date, montant
         FROM LigneFraisHorsForfait
         WHERE idVisiteur = ? AND mois = ?
         ORDER BY date'
    );
    $stmt->execute([$idVisiteur, $selectedMois]);
    $horsForfaitLignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($horsForfaitLignes as $ligne) {
        $totalHorsForfait += (float) $ligne['montant'];
    }

    $stmt = $bdd->prepare(
        'SELECT e.libelle AS etat
         FROM FicheFrais AS ff
         JOIN Etat AS e ON ff.idEtat = e.id
         WHERE ff.idVisiteur = ? AND ff.mois = ?'
    );
    $stmt->execute([$idVisiteur, $selectedMois]);
    $etatRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($etatRow) {
        $etatFiche = $etatRow['etat'];
    }

    $totalGlobal = $totalForfait + $totalHorsForfait;
} catch (PDOException $e) {
    $error = 'Erreur de lecture des frais : ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8">
    <title>Mes frais</title>
    <link rel="stylesheet" href="frais.css">
    <link href="images/logo.ico" rel="shortcut icon" type="image/x-icon" />
  </head>
  <body>
    <div class="header">
      <img src="images/logo.png" alt="Logo GSB" class="logo">
      <h1>Mes frais</h1>
      <a href="index.php" class="deconnexion">Déconnexion</a>
    </div>

    <div class="container">
      <!-- Menu déroulant en haut à gauche -->
      <div class="top-section">
        <div class="menu-dropdown">
          <button class="dropdown-btn">☰ Menu</button>
          <div class="dropdown-content">
            <a href="accueil_medical.php">Accueil</a>
            <a href="frais.php" class="active">Mes frais</a>
            <a href="saisi.php">Saisie des frais</a>
          </div>
        </div>

        <!-- Sélecteur de mois en haut à droite -->
        <div class="month-selector">
          <label>Mois :</label>
          <div class="month-dropdown">
            <button type="button" class="month-btn" id="current-month" data-month="<?= htmlspecialchars($currentMonthValue) ?>"><?= htmlspecialchars($currentMonthLabel) ?></button>
            <div class="month-list">
              <!-- Les mois seront ajoutés par JavaScript -->
            </div>
          </div>
        </div>
      </div>

      <!-- Tableau des frais -->
      <div class="frais-section">
        <h2>Frais du mois sélectionné</h2>
        <?php if ($error): ?>
            <div style="padding:12px; margin-bottom:16px; background:#f9e7e7; border:1px solid #a94442; color:#a94442;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <table class="frais-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Description</th>
              <th>Catégorie</th>
              <th>Montant</th>
              <th>Statut</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($forfaitLignes) && empty($horsForfaitLignes)): ?>
                <tr class="empty-row">
                  <td colspan="5" style="text-align: center; color: #999;">Aucun frais pour ce mois</td>
                </tr>
            <?php else: ?>
                <?php foreach ($forfaitLignes as $ligne): ?>
                    <tr>
                      <td>-</td>
                      <td><?= htmlspecialchars($ligne['libelle']) ?></td>
                      <td>Forfait</td>
                      <td><?= number_format($ligne['total'], 2, ',', ' ') ?> €</td>
                      <td><?= htmlspecialchars($etatFiche) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php foreach ($horsForfaitLignes as $ligne): ?>
                    <tr>
                      <td><?= htmlspecialchars((new DateTime($ligne['date']))->format('d/m/Y')) ?></td>
                      <td><?= htmlspecialchars($ligne['libelle']) ?></td>
                      <td>Hors forfait</td>
                      <td><?= number_format($ligne['montant'], 2, ',', ' ') ?> €</td>
                      <td><?= htmlspecialchars($etatFiche) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Total et état des frais -->
      <div class="summary-section">
        <div class="summary-card">
          <div class="summary-item">
            <label>Total des frais :</label>
            <span class="total-amount"><?= number_format($totalGlobal, 2, ',', ' ') ?> €</span>
          </div>
          <div class="summary-item">
            <label>État des frais :</label>
            <span class="status-badge"><?= htmlspecialchars($etatFiche) ?></span>
          </div>
        </div>
      </div>
    </div>

    <div class="footer">
        <p>&copy; 2026 GSB. Tous droits réservés.</p>
        <a href="mentions.php">Mentions légales</a>
    </div>

    <script src="frais.js"></script>
  </body>
</html>
