<?php
$fiche         = $resultat['fiche'];
$lignesForfait = $resultat['lignesForfait'];
$lignesHors    = $resultat['lignesHors'];
$moisListe     = $resultat['moisListe'];
$modifiable    = $fiche !== null && $fiche['idEtat'] === 'CR';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Saisie des frais</title>
    <link rel="stylesheet" href="<?= base_url('css/saisi.css') ?>">
    <link href="<?= base_url('images/logo.ico') ?>" rel="shortcut icon" type="image/x-icon">
</head>
<body>

    <div class="header">
        <img src="<?= base_url('images/logo.png') ?>" alt="Logo GSB" class="logo">
        <h1>Saisie des frais</h1>
        <a href="<?= base_url('getdata?action=deconnexion') ?>" class="deconnexion">Déconnexion</a>
    </div>

    <div class="container">

        <?php if (! empty($message)) : ?>
            <div style="padding:12px; margin-bottom:16px; background:#e7f7e7; border:1px solid #3c763d; color:#2a5422;">
                <?= esc($message) ?>
            </div>
        <?php endif; ?>

        <?php if (! empty($erreur)) : ?>
            <div style="padding:12px; margin-bottom:16px; background:#f9e7e7; border:1px solid #a94442; color:#a94442;">
                <?= esc($erreur) ?>
            </div>
        <?php endif; ?>

        <div class="top-section">
            <div class="menu-dropdown">
                <button class="dropdown-btn">☰ Menu</button>
                <div class="dropdown-content">
                    <a href="<?= base_url('/') ?>">Accueil</a>
                    <a href="<?= base_url('getdata?action=consulter&mois=' . $mois) ?>">Mes frais</a>
                    <a href="<?= base_url('getdata?action=saisir&mois=' . $mois) ?>" class="active">Saisie des frais</a>
                </div>
            </div>

            <div class="month-selector">
                <label>Mois :</label>
                <form method="get" action="<?= base_url('getdata') ?>">
                    <input type="hidden" name="action" value="saisir">
                    <select name="mois" onchange="this.form.submit()">
                        <?php
                        // mois courant + 11 mois precedents
                        $date = new DateTime('first day of this month');
                        for ($i = 0; $i < 12; $i++) :
                            $valeur = $date->format('Ym');
                        ?>
                            <option value="<?= $valeur ?>" <?= $valeur === $mois ? 'selected' : '' ?>>
                                <?= $date->format('m/Y') ?>
                            </option>
                        <?php
                            $date->modify('-1 month');
                        endfor;
                        ?>
                    </select>
                </form>
            </div>
        </div>

        <!-- ============ FRAIS FORFAITISES ============ -->
        <div class="frais-section">
            <div class="forfait-card">
                <h2 class="card-title">Frais forfaitaire</h2>

                <form method="post" action="<?= base_url('postdata') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="enregistrerForfait">
                    <input type="hidden" name="mois" value="<?= esc($mois) ?>">

                    <div class="forfait-grid">
                        <?php foreach ($lignesForfait as $ligne) : ?>
                            <div class="forfait-item">
                                <label for="q<?= esc($ligne['idFraisForfait']) ?>">
                                    <?= esc($ligne['libelle']) ?>
                                    (<?= number_format((float) $ligne['montant'], 2, ',', ' ') ?> €)
                                </label>
                                <input type="number"
                                       id="q<?= esc($ligne['idFraisForfait']) ?>"
                                       name="quantite[<?= esc($ligne['idFraisForfait']) ?>]"
                                       value="<?= esc($ligne['quantite']) ?>"
                                       min="0" step="1" inputmode="numeric" pattern="[0-9]*"
                                       <?= $modifiable ? '' : 'disabled' ?>>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($modifiable) : ?>
                        <div class="footer-section">
                            <button type="submit" class="btn-enregistrer">Enregistrer les frais forfaitisés</button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- ============ FRAIS HORS FORFAIT ============ -->
        <div class="hors-forfait-section">
            <div class="hors-forfait-card">
                <h2 class="card-title">Hors forfait</h2>

                <?php if ($modifiable) : ?>
                    <form method="post" action="<?= base_url('postdata') ?>" class="hors-forfait-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="ajouterHorsForfait">
                        <input type="hidden" name="mois" value="<?= esc($mois) ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="libelle">Libellé</label>
                                <input type="text" id="libelle" name="libelle" maxlength="100"
                                       placeholder="Description du frais" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="date-frais">Date</label>
                                <input type="date" id="date-frais" name="date"
                                       max="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="montant-frais">Montant</label>
                                <input type="number" id="montant-frais" name="montant"
                                       min="0.01" step="0.01" inputmode="decimal" required>
                                <span class="unit">€</span>
                            </div>
                        </div>

                        <button type="submit" class="btn-ajouter">Ajouter frais</button>
                    </form>
                <?php endif; ?>

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
                        <tbody>
                            <?php if (empty($lignesHors)) : ?>
                                <tr><td colspan="4">Aucun frais hors forfait pour ce mois.</td></tr>
                            <?php endif; ?>

                            <?php foreach ($lignesHors as $ligne) : ?>
                                <tr>
                                    <td><?= esc((new DateTime($ligne['date']))->format('d/m/Y')) ?></td>
                                    <td><?= esc($ligne['libelle']) ?></td>
                                    <td><?= number_format((float) $ligne['montant'], 2, ',', ' ') ?> €</td>
                                    <td>
                                        <?php if ($modifiable) : ?>
                                            <form method="post" action="<?= base_url('postdata') ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="supprimerHorsForfait">
                                                <input type="hidden" name="mois" value="<?= esc($mois) ?>">
                                                <input type="hidden" name="id" value="<?= esc($ligne['id']) ?>">
                                                <button type="submit">Supprimer</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="summary-section">
            <p><strong>Total de la fiche :</strong> <?= number_format($total, 2, ',', ' ') ?> €
               — <strong>État :</strong> <?= $fiche === null ? '-' : esc($fiche['libelleEtat']) ?></p>
        </div>

    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> GSB. Tous droits réservés.</p>
        <a href="<?= base_url('getdata?action=mentions') ?>">Mentions légales</a>
    </div>

</body>
</html>
