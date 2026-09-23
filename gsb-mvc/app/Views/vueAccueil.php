<?php
// Donnees transmises par le controleur
$fiche     = $resultat['fiche'];
$moisListe = $resultat['moisListe'];

// Affichage du mois au format "septembre 2026"
$libelleMois = \DateTime::createFromFormat('Ymd', $mois . '01')->format('m/Y');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link rel="stylesheet" href="<?= base_url('css/accueil_medical.css') ?>">
    <link href="<?= base_url('images/logo.ico') ?>" rel="shortcut icon" type="image/x-icon" />
</head>
<body>

    <header class="header">
        <div class="header-left">
            <img src="<?= base_url('images/logo.png') ?>" alt="Logo GSB" class="logo">
            <div>
                <p class="eyebrow">Accueil médical</p>
                <h1>Bonjour, <?= esc(session()->get('prenom')) ?></h1>
            </div>
        </div>
        <div class="header-actions">
            <a href="<?= base_url('getdata?action=deconnexion') ?>" class="deconnexion">Déconnexion</a>
        </div>
    </header>

    <div class="menu-dropdown">
        <button class="dropdown-btn" aria-expanded="false" aria-haspopup="true">☰ Menu</button>
        <div class="dropdown-content" role="menu">
            <a href="<?= base_url('/') ?>" class="active" role="menuitem">Accueil</a>
            <a href="<?= base_url('getdata?action=consulter&mois=' . $mois) ?>" role="menuitem">Mes frais</a>
            <a href="<?= base_url('getdata?action=saisir&mois=' . $mois) ?>" role="menuitem">Saisie des frais</a>
        </div>
    </div>

    <div class="main-content">
        <div class="consultation_frais">

            <div class="mois">
                <label for="mois-select">Période :</label>
                <form method="get" action="<?= base_url('getdata') ?>">
                    <input type="hidden" name="action" value="accueil">
                    <select id="mois-select" name="mois" onchange="this.form.submit()">
                        <?php if (empty($moisListe)) : ?>
                            <option value="<?= esc($mois) ?>"><?= esc($libelleMois) ?></option>
                        <?php endif; ?>
                        <?php foreach ($moisListe as $ligne) : ?>
                            <option value="<?= esc($ligne['mois']) ?>" <?= $ligne['mois'] === $mois ? 'selected' : '' ?>>
                                <?= esc(substr($ligne['mois'], 4, 2) . '/' . substr($ligne['mois'], 0, 4)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <div class="etat">
                <label>État :</label>
                <div class="etat-static" id="etat-display">
                    <?= $fiche === null ? 'Aucune fiche pour ce mois' : esc($fiche['libelleEtat']) ?>
                </div>
            </div>

            <div class="total_frais">
                <label>Total des frais :</label>
                <span id="total-frais" class="total-amount"><?= number_format($total, 2, ',', ' ') ?> €</span>
            </div>

        </div>
    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> GSB. Tous droits réservés.</p>
        <a href="<?= base_url('getdata?action=mentions') ?>">Mentions légales</a>
    </div>

</body>
</html>
