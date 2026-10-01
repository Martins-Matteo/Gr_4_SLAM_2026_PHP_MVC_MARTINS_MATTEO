<?php
session_start();
require 'config.php';

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

$message = '';
$error = '';

function parseMois(string $mois): ?string {
    $mois = trim($mois);
    if (preg_match('/^[0-9]{6}$/', $mois)) {
        return $mois;
    }
    if (preg_match('/^([0-9]{4})-([0-9]{2})$/', $mois, $matches)) {
        return $matches[1] . $matches[2];
    }
    $mois = mb_strtolower(trim(preg_replace('/\s+/', ' ', $mois)), 'UTF-8');
    $map = [
        'janvier' => '01', 'février' => '02', 'fevrier' => '02', 'mars' => '03', 'avril' => '04',
        'mai' => '05', 'juin' => '06', 'juillet' => '07', 'août' => '08', 'aout' => '08',
        'septembre' => '09', 'octobre' => '10', 'novembre' => '11', 'décembre' => '12'
    ];
    if (preg_match('/^([a-zàâéèêôûùç]+)\s+([0-9]{4})$/u', $mois, $matches)) {
        $nomMois = $matches[1];
        $annee = $matches[2];
        if (isset($map[$nomMois])) {
            return $annee . $map[$nomMois];
        }
    }
    return null;
}

$selectedMois = $_POST['mois'] ?? date('Ym');
$selectedMois = parseMois($selectedMois) ?? date('Ym');
$currentMonth = DateTime::createFromFormat('Ym', $selectedMois);
if (!$currentMonth) {
    $currentMonth = new DateTime();
    $selectedMois = $currentMonth->format('Ym');
}
$currentMonthLabel = $currentMonth->format('F Y');
$currentMonthValue = $currentMonth->format('Y-m');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idVisiteur = $_SESSION['id'];
    $mois = parseMois($_POST['mois'] ?? '');
    $nuitee = isset($_POST['nuitee']) ? floatval(str_replace(',', '.', $_POST['nuitee'])) : 0.0;
    $repas = isset($_POST['repas']) ? floatval(str_replace(',', '.', $_POST['repas'])) : 0.0;
    $trajet = isset($_POST['trajet']) ? intval($_POST['trajet']) : 0;
    $horsJson = $_POST['hors_forfait_json'] ?? '[]';
    $horsForfait = json_decode($horsJson, true);
    if (!is_array($horsForfait)) {
        $horsForfait = [];
    }

    if (!$mois) {
        $error = 'Mois invalide.';
    } elseif ($nuitee < 0 || $repas < 0 || $trajet < 0) {
        $error = 'Les valeurs de frais doivent être positives.';
    } else {
        try {
            $bdd->beginTransaction();

            $stmt = $bdd->prepare('SELECT 1 FROM FicheFrais WHERE idVisiteur = ? AND mois = ?');
            $stmt->execute([$idVisiteur, $mois]);
            if (!$stmt->fetch()) {
                $insertFiche = $bdd->prepare('INSERT INTO FicheFrais (idVisiteur, mois, nbJustificatifs, montantValide, dateModif, idEtat) VALUES (?, ?, 0, 0.00, ?, ?)');
                $insertFiche->execute([$idVisiteur, $mois, date('Y-m-d'), 'CR']);
            }

            // Insérer trajet comme quantité dans LigneFraisForfait
            if ($trajet > 0) {
                $stmtForfait = $bdd->prepare('INSERT IGNORE INTO LigneFraisForfait (idVisiteur, mois, idFraisForfait, quantite) VALUES (?, ?, ?, ?)');
                $stmtForfait->execute([$idVisiteur, $mois, 'KM', $trajet]);
            }

            // Insérer nuitée et repas comme montants dans LigneFraisHorsForfait
            $stmtHors = $bdd->prepare('INSERT INTO LigneFraisHorsForfait (idVisiteur, mois, libelle, date, montant) VALUES (?, ?, ?, ?, ?)');
            if ($nuitee > 0) {
                $stmtHors->execute([$idVisiteur, $mois, 'Nuitée', date('Y-m-d'), $nuitee]);
            }
            if ($repas > 0) {
                $stmtHors->execute([$idVisiteur, $mois, 'Repas', date('Y-m-d'), $repas]);
            }

            // Insérer les autres hors forfait
            foreach ($horsForfait as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $libelle = trim($item['libelle'] ?? '');
                $date = trim($item['date'] ?? '');
                $montant = isset($item['montant']) ? floatval(str_replace(',', '.', $item['montant'])) : 0.0;
                if ($libelle === '' || $date === '' || $montant <= 0) {
                    continue;
                }
                $stmtHors->execute([$idVisiteur, $mois, $libelle, $date, $montant]);
            }

            $bdd->commit();
            $message = 'Les frais ont été enregistrés en base de données.';
        } catch (PDOException $e) {
            $bdd->rollBack();
            $error = 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Saisie des frais</title>
    <link rel="stylesheet" href="saisi.css">
    <link href="images/logo.ico" rel="shortcut icon" type="image/x-icon">
</head>
<body>
    <div class="header">
        <img src="images/logo.png" alt="Logo GSB" class="logo">
        <h1>Saisie des frais</h1>
        <a href="index.php" class="deconnexion">Déconnexion</a>
    </div>

    <div class="container">
        <form id="form-saisi" method="post" action="saisi.php">
            <input type="hidden" name="mois" id="mois-input" value="<?= htmlspecialchars($selectedMois) ?>">
            <input type="hidden" name="hors_forfait_json" id="hors-forfait-json" value="">
            <?php if ($message): ?>
                <div style="padding:12px; margin-bottom:16px; background:#e7f7e7; border:1px solid #3c763d; color:#2a5422;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php elseif ($error): ?>
                <div style="padding:12px; margin-bottom:16px; background:#f9e7e7; border:1px solid #a94442; color:#a94442;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="top-section">
                <div class="menu-dropdown">
                <button class="dropdown-btn">☰ Menu</button>
                <div class="dropdown-content">
                    <a href="accueil_medical.php">Accueil</a>
                    <a href="frais.php">Mes frais</a>
                    <a href="saisi.php" class="active">Saisie des frais</a>
                </div>
            </div>

            <div class="month-selector">
                <label>Mois :</label>
                <div class="month-dropdown">
                    <button type="button" class="month-btn" id="current-month" data-month="<?= htmlspecialchars($currentMonthValue) ?>"><?= htmlspecialchars($currentMonthLabel) ?></button>
                    <div class="month-list"></div>
                </div>
            </div>
        </div>

        <div class="frais-section">
            <div class="forfait-card">
                <h2 class="card-title">Frais forfaitaire</h2>
                <div class="forfait-grid">
                    <div class="forfait-item">
                        <label for="nuitee">Nuitée</label>
                        <input type="number" id="nuitee" name="nuitee" placeholder="0" min="0" step="0.01">
                        <span class="unit">€</span>
                    </div>
                    <div class="forfait-item">
                        <label for="repas">Repas</label>
                        <input type="number" id="repas" name="repas" placeholder="0" min="0" step="0.01">
                        <span class="unit">€</span>
                    </div>
                    <div class="forfait-item">
                        <label for="trajet">Trajet</label>
                        <input type="number" id="trajet" name="trajet" placeholder="0" min="0" step="1" inputmode="numeric" pattern="[0-9]*">
                        <span class="unit">km</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="hors-forfait-section">
            <div class="hors-forfait-card">
                <h2 class="card-title">Hors forfait</h2>
                <div class="hors-forfait-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="libelle">Libellé</label>
                            <input type="text" id="libelle" placeholder="Description du frais">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date-frais">Date</label>
                            <input type="date" id="date-frais">
                        </div>
                        <div class="form-group">
                            <label for="montant-frais">Montant</label>
                            <input type="number" id="montant-frais" placeholder="0" min="0" step="0.01" inputmode="decimal" pattern="[0-9]*([\.,][0-9]+)?">
                            <span class="unit">€</span>
                        </div>
                    </div>
                    <button type="button" class="btn-ajouter" id="btn-ajouter">Ajouter frais</button>
                </div>

                <div class="hors-forfait-list">
                    <h3>Frais ajoutés</h3>
                    <table class="hors-forfait-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Libellé</th>
                                <th>Montant</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="footer-section">
            <button type="submit" class="btn-enregistrer">Enregistrer frais</button>
        </div>
        </form>
    </div>

    <div class="footer">
        <p>&copy; 2026 GSB. Tous droits réservés.</p>
        <a href="mentions.php">Mentions légales</a>
    </div>

    <script src="saisi.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('form-saisi');
        if (!form) {
            return;
        }

        form.addEventListener('submit', function() {
            const horsField = document.getElementById('hors-forfait-json');
            const monthField = document.getElementById('mois-input');
            if (horsField) {
                horsField.value = JSON.stringify(window.fraisHorsForfait || []);
            }
            if (monthField) {
                const monthButton = document.getElementById('current-month');
                let monthValue = '';
                if (monthButton) {
                    monthValue = monthButton.dataset.month || monthButton.textContent;
                }
                monthField.value = monthValue.replace('-', '');
            }
        });
    });
    </script>
</body>
</html>
