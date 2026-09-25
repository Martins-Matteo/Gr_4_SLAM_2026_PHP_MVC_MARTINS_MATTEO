<?php
$fiche         = $resultat['fiche'];
$lignesForfait = $resultat['lignesForfait'];
$lignesHors    = $resultat['lignesHors'];
$moisListe     = $resultat['moisListe'];

echo view('templates/entete', ['titrePage' => 'Mes frais', 'menuActif' => 'consulter', 'mois' => $mois]);
?>

<div class="carte">
    <h2>Fiche de frais</h2>

    <form method="get" action="<?= base_url('getdata') ?>" class="champ">
        <input type="hidden" name="action" value="consulter">
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

    <?php if ($fiche === null) : ?>
        <p class="vide">Aucune fiche de frais pour ce mois.</p>
    <?php else : ?>
        <div class="grille">
            <div class="indicateur">
                <span class="libelle">État</span>
                <span class="etat"><?= esc($fiche['libelleEtat']) ?></span>
            </div>
            <div class="indicateur">
                <span class="libelle">Total</span>
                <span class="valeur"><?= number_format($total, 2, ',', ' ') ?> €</span>
            </div>
            <div class="indicateur">
                <span class="libelle">Dernière modification</span>
                <span class="valeur" style="font-size:1.05rem">
                    <?= esc((new DateTime($fiche['dateModif']))->format('d/m/Y')) ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($fiche !== null) : ?>
    <div class="carte">
        <h2>Frais forfaitisés</h2>
        <table>
            <thead>
                <tr>
                    <th>Type de frais</th>
                    <th class="nombre">Quantité</th>
                    <th class="nombre">Montant unitaire</th>
                    <th class="nombre">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lignesForfait as $ligne) : ?>
                    <tr>
                        <td><?= esc($ligne['libelle']) ?></td>
                        <td class="nombre"><?= esc($ligne['quantite']) ?></td>
                        <td class="nombre"><?= number_format((float) $ligne['montant'], 2, ',', ' ') ?> €</td>
                        <td class="nombre"><?= number_format((float) $ligne['total'], 2, ',', ' ') ?> €</td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($lignesForfait)) : ?>
                    <tr><td colspan="4" class="vide">Aucun frais forfaitisé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="carte">
        <h2>Frais hors forfait</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Libellé</th>
                    <th class="nombre">Montant</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lignesHors as $ligne) : ?>
                    <tr>
                        <td><?= esc((new DateTime($ligne['date']))->format('d/m/Y')) ?></td>
                        <td><?= esc($ligne['libelle']) ?></td>
                        <td class="nombre"><?= number_format((float) $ligne['montant'], 2, ',', ' ') ?> €</td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($lignesHors)) : ?>
                    <tr><td colspan="3" class="vide">Aucun frais hors forfait.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?= view('templates/pied') ?>
