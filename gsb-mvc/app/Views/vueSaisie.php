<?php
$fiche         = $resultat['fiche'];
$lignesForfait = $resultat['lignesForfait'];
$lignesHors    = $resultat['lignesHors'];
$modifiable    = $fiche !== null && $fiche['idEtat'] === 'CR';

echo view('templates/entete', ['titrePage' => 'Saisie des frais', 'menuActif' => 'saisir', 'mois' => $mois]);
?>

<?php if (! empty($message)) : ?>
    <div class="message message-succes"><?= esc($message) ?></div>
<?php endif; ?>

<?php if (! empty($erreur)) : ?>
    <div class="message message-erreur"><?= esc($erreur) ?></div>
<?php endif; ?>

<div class="carte">
    <h2>Mois de saisie</h2>

    <form method="get" action="<?= base_url('getdata') ?>" class="champ">
        <input type="hidden" name="action" value="saisir">
        <label for="mois">Période</label>
        <select id="mois" name="mois" onchange="this.form.submit()">
            <?php
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

    <p>
        État de la fiche :
        <span class="etat"><?= $fiche === null ? 'Aucune fiche' : esc($fiche['libelleEtat']) ?></span>
        <?php if (! $modifiable && $fiche !== null) : ?>
            — cette fiche n'est plus modifiable.
        <?php endif; ?>
    </p>

    <?php if ($modifiable) : ?>
        <form method="post" action="<?= base_url('postdata') ?>"
              onsubmit="return confirm('Clôturer cette fiche ? Vous ne pourrez plus la modifier.');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="cloturerFiche">
            <input type="hidden" name="mois" value="<?= esc($mois) ?>">
            <button type="submit" class="bouton-secondaire">Clôturer et transmettre à la comptabilité</button>
        </form>
    <?php endif; ?>
</div>

<!-- =================== FRAIS FORFAITISÉS =================== -->
<div class="carte">
    <h2>Frais forfaitisés</h2>

    <form method="post" action="<?= base_url('postdata') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="enregistrerForfait">
        <input type="hidden" name="mois" value="<?= esc($mois) ?>">

        <div class="grille">
            <?php foreach ($lignesForfait as $ligne) : ?>
                <div class="champ">
                    <label for="q<?= esc($ligne['idFraisForfait']) ?>">
                        <?= esc($ligne['libelle']) ?>
                        (<?= number_format((float) $ligne['montant'], 2, ',', ' ') ?> €)
                    </label>
                    <input type="number"
                           id="q<?= esc($ligne['idFraisForfait']) ?>"
                           name="quantite[<?= esc($ligne['idFraisForfait']) ?>]"
                           value="<?= esc($ligne['quantite']) ?>"
                           min="0" step="1" inputmode="numeric"
                           <?= $modifiable ? '' : 'disabled' ?>>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($modifiable) : ?>
            <button type="submit">Enregistrer les quantités</button>
        <?php endif; ?>
    </form>
</div>

<!-- =================== FRAIS HORS FORFAIT =================== -->
<div class="carte">
    <h2>Ajouter un frais hors forfait</h2>

    <?php if ($modifiable) : ?>
        <form method="post" action="<?= base_url('postdata') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="ajouterHorsForfait">
            <input type="hidden" name="mois" value="<?= esc($mois) ?>">

            <div class="grille">
                <div class="champ">
                    <label for="libelle">Libellé</label>
                    <input type="text" id="libelle" name="libelle" maxlength="100"
                           placeholder="Ex. : taxi gare de Lorient" required>
                </div>
                <div class="champ">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" max="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="champ">
                    <label for="montant">Montant (€)</label>
                    <input type="number" id="montant" name="montant" min="0.01" step="0.01" required>
                </div>
            </div>

            <button type="submit" class="bouton-secondaire">Ajouter le frais</button>
        </form>
    <?php else : ?>
        <p class="vide">La fiche n'est plus modifiable : aucun ajout possible.</p>
    <?php endif; ?>
</div>

<div class="carte">
    <h2>Frais hors forfait déjà saisis</h2>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Libellé</th>
                <th class="nombre">Montant</th>
                <th class="nombre">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lignesHors as $ligne) : ?>
                <tr>
                    <td><?= esc((new DateTime($ligne['date']))->format('d/m/Y')) ?></td>
                    <td><?= esc($ligne['libelle']) ?></td>
                    <td class="nombre"><?= number_format((float) $ligne['montant'], 2, ',', ' ') ?> €</td>
                    <td class="nombre">
                        <?php if ($modifiable) : ?>
                            <form method="post" action="<?= base_url('postdata') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="supprimerHorsForfait">
                                <input type="hidden" name="mois" value="<?= esc($mois) ?>">
                                <input type="hidden" name="id" value="<?= esc($ligne['id']) ?>">
                                <button type="submit" class="bouton-discret">Supprimer</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($lignesHors)) : ?>
                <tr><td colspan="4" class="vide">Aucun frais hors forfait pour ce mois.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p style="text-align:right; margin-bottom:0">
        <strong>Total de la fiche : <?= number_format($total, 2, ',', ' ') ?> €</strong>
    </p>
</div>

<?= view('templates/pied') ?>
