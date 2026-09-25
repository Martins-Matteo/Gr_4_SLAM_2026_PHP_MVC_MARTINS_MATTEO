<?php
$visiteur      = $resultat['visiteur'];
$fiche         = $resultat['fiche'];
$lignesForfait = $resultat['lignesForfait'];
$lignesHors    = $resultat['lignesHors'];

echo view('templates/entete', ['titrePage' => 'Fiche de frais', 'menuActif' => 'comptable']);
?>

<?php if (! empty($message)) : ?>
    <div class="message message-succes"><?= esc($message) ?></div>
<?php endif; ?>

<?php if (! empty($erreur)) : ?>
    <div class="message message-erreur"><?= esc($erreur) ?></div>
<?php endif; ?>

<p><a class="bouton bouton-discret" href="<?= base_url('getdata?action=comptable') ?>">← Retour à la liste</a></p>

<div class="carte">
    <h2>
        <?= $visiteur === null ? esc($idVisiteur) : esc($visiteur['nom'] . ' ' . $visiteur['prenom']) ?>
        — <?= esc(substr($mois, 4, 2) . '/' . substr($mois, 0, 4)) ?>
    </h2>

    <div class="grille">
        <div class="indicateur">
            <span class="libelle">État</span>
            <span class="etat"><?= esc($fiche['libelleEtat']) ?></span>
        </div>
        <div class="indicateur">
            <span class="libelle">Total saisi</span>
            <span class="valeur"><?= number_format($total, 2, ',', ' ') ?> €</span>
        </div>
        <div class="indicateur">
            <span class="libelle">Montant validé</span>
            <span class="valeur">
                <?= in_array($fiche['idEtat'], ['VA', 'RB'], true)
                    ? number_format((float) $fiche['montantValide'], 2, ',', ' ') . ' €'
                    : '—' ?>
            </span>
        </div>
        <div class="indicateur">
            <span class="libelle">Justificatifs</span>
            <span class="valeur"><?= esc($fiche['nbJustificatifs']) ?></span>
        </div>
    </div>
</div>

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

<!-- ================== ACTIONS DU COMPTABLE ================== -->
<?php if ($fiche['idEtat'] === 'CL') : ?>
    <div class="carte">
        <h2>Valider la fiche</h2>
        <p>Vérifiez les justificatifs reçus, puis enregistrez le montant retenu pour le paiement.</p>

        <form method="post" action="<?= base_url('postdata') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="validerFiche">
            <input type="hidden" name="visiteur" value="<?= esc($idVisiteur) ?>">
            <input type="hidden" name="mois" value="<?= esc($mois) ?>">

            <div class="grille">
                <div class="champ">
                    <label for="montantValide">Montant validé (€)</label>
                    <input type="number" id="montantValide" name="montantValide"
                           min="0" step="0.01" value="<?= number_format($total, 2, '.', '') ?>" required>
                </div>
                <div class="champ">
                    <label for="nbJustificatifs">Justificatifs reçus</label>
                    <input type="number" id="nbJustificatifs" name="nbJustificatifs"
                           min="0" step="1" value="<?= esc($fiche['nbJustificatifs']) ?>" required>
                </div>
            </div>

            <button type="submit">Valider et mettre en paiement</button>
        </form>
    </div>

<?php elseif ($fiche['idEtat'] === 'VA') : ?>
    <div class="carte">
        <h2>Remboursement</h2>
        <p>La fiche est validée pour un montant de
           <strong><?= number_format((float) $fiche['montantValide'], 2, ',', ' ') ?> €</strong>.
           Marquez-la comme remboursée une fois le virement effectué.</p>

        <form method="post" action="<?= base_url('postdata') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="rembourserFiche">
            <input type="hidden" name="visiteur" value="<?= esc($idVisiteur) ?>">
            <input type="hidden" name="mois" value="<?= esc($mois) ?>">
            <button type="submit">Marquer comme remboursée</button>
        </form>
    </div>

<?php elseif ($fiche['idEtat'] === 'CR') : ?>
    <div class="carte">
        <p class="vide">Le visiteur n'a pas encore clôturé cette fiche : elle n'est pas prête à être validée.</p>
    </div>

<?php else : ?>
    <div class="carte">
        <p class="vide">Cette fiche a été remboursée : aucune action n'est possible.</p>
    </div>
<?php endif; ?>

<?= view('templates/pied') ?>
