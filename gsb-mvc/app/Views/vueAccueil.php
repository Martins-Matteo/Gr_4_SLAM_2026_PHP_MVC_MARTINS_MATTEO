<?php
$fiche     = $resultat['fiche'];
$moisListe = $resultat['moisListe'];

$titrePage = 'Accueil';
$menuActif = 'accueil';
echo view('templates/entete', ['titrePage' => $titrePage, 'menuActif' => $menuActif, 'mois' => $mois]);
?>

<div class="carte">
    <h2>Fiche de frais du mois</h2>

    <form method="get" action="<?= base_url('getdata') ?>" class="champ">
        <input type="hidden" name="action" value="accueil">
        <label for="mois">Période</label>
        <select id="mois" name="mois" onchange="this.form.submit()">
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

    <div class="grille">
        <div class="indicateur">
            <span class="libelle">État de la fiche</span>
            <span class="etat"><?= $fiche === null ? 'Aucune fiche' : esc($fiche['libelleEtat']) ?></span>
        </div>
        <div class="indicateur">
            <span class="libelle">Total des frais</span>
            <span class="valeur"><?= number_format($total, 2, ',', ' ') ?> €</span>
        </div>
        <div class="indicateur">
            <span class="libelle">Justificatifs déposés</span>
            <span class="valeur"><?= $fiche === null ? '0' : esc($fiche['nbJustificatifs']) ?></span>
        </div>
    </div>
</div>

<div class="carte">
    <h2>Que voulez-vous faire ?</h2>
    <p>
        <a class="bouton" href="<?= base_url('getdata?action=saisir&mois=' . $mois) ?>">Saisir mes frais</a>
        <a class="bouton bouton-secondaire" href="<?= base_url('getdata?action=consulter&mois=' . $mois) ?>">Consulter le détail</a>
    </p>
</div>

<?= view('templates/pied') ?>
