<?php
$fiche         = $resultat['fiche'];
$lignesForfait = $resultat['lignesForfait'];
$lignesHors    = $resultat['lignesHors'];
$moisListe     = $resultat['moisListe'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes frais</title>
    <link rel="stylesheet" href="<?= base_url('css/frais.css') ?>">
    <link href="<?= base_url('images/logo.ico') ?>" rel="shortcut icon" type="image/x-icon" />
</head>
<body>

    <div class="header">
        <img src="<?= base_url('images/logo.png') ?>" alt="Logo GSB" class="logo">
        <h1>Mes frais</h1>
        <a href="<?= base_url('getdata?action=deconnexion') ?>" class="deconnexion">Déconnexion</a>
    </div>

    <div class="container">

        <div class="top-section">
            <div class="menu-dropdown">
                <button class="dropdown-btn">☰ Menu</button>
                <div class="dropdown-content">
                    <a href="<?= base_url('/') ?>">Accueil</a>
                    <a href="<?= base_url('getdata?action=consulter&mois=' . $mois) ?>" class="active">Mes frais</a>
                    <a href="<?= base_url('getdata?action=saisir&mois=' . $mois) ?>">Saisie des frais</a>
                </div>
            </div>

            <div class="month-selector">
                <label>Mois :</label>
                <form method="get" action="<?= base_url('getdata') ?>">
                    <input type="hidden" name="action" value="consulter">
                    <select name="mois" onchange="this.form.submit()">
                        <?php if (empty($moisListe)) : ?>
                            <option value="<?= esc($mois) ?>"><?= esc(substr($mois, 4, 2) . '/' . substr($mois, 0, 4)) ?></option>
                        <?php endif; ?>
                        <?php foreach ($moisListe as $ligne) : ?>
                            <option value="<?= esc($ligne['mois']) ?>" <?= $ligne['mois'] === $mois ? 'selected' : '' ?>>
                                <?= esc(substr($ligne['mois'], 4, 2) . '/' . substr($ligne['mois'], 0, 4)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <div class="frais-section">
            <h2>Frais du mois sélectionné</h2>

            <?php if ($fiche === null) : ?>
                <p>Aucune fiche de frais pour ce mois.</p>
            <?php else : ?>
                <table class="frais-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Catégorie</th>
                            <th>Quantité</th>
                            <th>Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lignesForfait as $ligne) : ?>
                            <tr>
                                <td>-</td>
                                <td><?= esc($ligne['libelle']) ?></td>
                                <td>Forfait</td>
                                <td><?= esc($ligne['quantite']) ?></td>
                                <td><?= number_format((float) $ligne['total'], 2, ',', ' ') ?> €</td>
                            </tr>
                        <?php endforeach; ?>

                        <?php foreach ($lignesHors as $ligne) : ?>
                            <tr>
                                <td><?= esc((new DateTime($ligne['date']))->format('d/m/Y')) ?></td>
                                <td><?= esc($ligne['libelle']) ?></td>
                                <td>Hors forfait</td>
                                <td>1</td>
                                <td><?= number_format((float) $ligne['montant'], 2, ',', ' ') ?> €</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="summary-section">
            <div class="summary-card">
                <div class="summary-item">
                    <label>Total des frais :</label>
                    <span class="total-amount"><?= number_format($total, 2, ',', ' ') ?> €</span>
                </div>
                <div class="summary-item">
                    <label>État des frais :</label>
                    <span class="status-badge">
                        <?= $fiche === null ? '-' : esc($fiche['libelleEtat']) ?>
                    </span>
                </div>
            </div>
        </div>

    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> GSB. Tous droits réservés.</p>
        <a href="<?= base_url('getdata?action=mentions') ?>">Mentions légales</a>
    </div>

</body>
</html>
