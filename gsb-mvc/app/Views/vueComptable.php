<?php
$fiches    = $resultat['fiches'];
$visiteurs = $resultat['visiteurs'];
$etats     = $resultat['etats'];

echo view('templates/entete', ['titrePage' => 'Suivi des fiches de frais', 'menuActif' => 'comptable']);
?>

<?php if (! empty($message)) : ?>
    <div class="message message-succes"><?= esc($message) ?></div>
<?php endif; ?>

<?php if (! empty($erreur)) : ?>
    <div class="message message-erreur"><?= esc($erreur) ?></div>
<?php endif; ?>

<div class="carte">
    <h2>Rechercher une fiche</h2>

    <form method="get" action="<?= base_url('getdata') ?>">
        <input type="hidden" name="action" value="comptable">

        <div class="grille">
            <div class="champ">
                <label for="visiteur">Visiteur médical</label>
                <select id="visiteur" name="visiteur">
                    <option value="">Tous les visiteurs</option>
                    <?php foreach ($visiteurs as $v) : ?>
                        <option value="<?= esc($v['id']) ?>" <?= $v['id'] === $idVisiteur ? 'selected' : '' ?>>
                            <?= esc($v['nom'] . ' ' . $v['prenom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="champ">
                <label for="etat">État de la fiche</label>
                <select id="etat" name="etat">
                    <option value="">Tous les états</option>
                    <?php foreach ($etats as $e) : ?>
                        <option value="<?= esc($e['id']) ?>" <?= $e['id'] === $idEtat ? 'selected' : '' ?>>
                            <?= esc($e['libelle']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="champ" style="align-self:end">
                <button type="submit">Filtrer</button>
                <a class="bouton bouton-secondaire" href="<?= base_url('getdata?action=comptable') ?>">Réinitialiser</a>
            </div>
        </div>
    </form>
</div>

<div class="carte">
    <h2><?= count($fiches) ?> fiche<?= count($fiches) > 1 ? 's' : '' ?> trouvée<?= count($fiches) > 1 ? 's' : '' ?></h2>

    <table>
        <thead>
            <tr>
                <th>Mois</th>
                <th>Visiteur</th>
                <th>État</th>
                <th class="nombre">Justificatifs</th>
                <th class="nombre">Total saisi</th>
                <th class="nombre">Montant validé</th>
                <th class="nombre">Détail</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fiches as $f) : ?>
                <tr>
                    <td><?= esc(substr($f['mois'], 4, 2) . '/' . substr($f['mois'], 0, 4)) ?></td>
                    <td><?= esc($f['nom'] . ' ' . $f['prenom']) ?></td>
                    <td><span class="etat"><?= esc($f['libelleEtat']) ?></span></td>
                    <td class="nombre"><?= esc($f['nbJustificatifs']) ?></td>
                    <td class="nombre"><?= number_format((float) $f['montantTotal'], 2, ',', ' ') ?> €</td>
                    <td class="nombre">
                        <?= $f['idEtat'] === 'CR' || $f['idEtat'] === 'CL'
                            ? '—'
                            : number_format((float) $f['montantValide'], 2, ',', ' ') . ' €' ?>
                    </td>
                    <td class="nombre">
                        <a class="bouton bouton-discret"
                           href="<?= base_url('getdata?action=comptableFiche&visiteur=' . urlencode($f['idVisiteur']) . '&mois=' . urlencode($f['mois'])) ?>">
                            Ouvrir
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($fiches)) : ?>
                <tr><td colspan="7" class="vide">Aucune fiche ne correspond à cette recherche.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= view('templates/pied') ?>
