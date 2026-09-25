<?php
$comptes = $resultat['comptes'];
$types   = $resultat['types'];

// $compte est non nul quand on modifie un compte existant
$modification = $compte !== null && ! empty($compte['idOrigine']);

echo view('templates/entete', ['titrePage' => 'Administration des comptes', 'menuActif' => 'admin']);
?>

<?php if (! empty($message)) : ?>
    <div class="message message-succes"><?= esc($message) ?></div>
<?php endif; ?>

<?php if (! empty($erreur)) : ?>
    <div class="message message-erreur"><?= esc($erreur) ?></div>
<?php endif; ?>

<div class="carte">
    <h2>Type de compte</h2>
    <p>
        <?php foreach ($types as $cle => $libelle) : ?>
            <a class="bouton <?= $cle === $type ? '' : 'bouton-secondaire' ?>"
               href="<?= base_url('getdata?action=admin&type=' . $cle) ?>">
                <?= esc($libelle) ?>
            </a>
        <?php endforeach; ?>
    </p>
</div>

<div class="carte">
    <h2><?= $modification ? 'Modifier le compte ' . esc($compte['id']) : 'Ajouter un compte' ?></h2>

    <form method="post" action="<?= base_url('postdata') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="adminEnregistrer">
        <input type="hidden" name="type" value="<?= esc($type) ?>">
        <input type="hidden" name="idOrigine" value="<?= $modification ? esc($compte['idOrigine']) : '' ?>">

        <div class="grille">
            <div class="champ">
                <label for="id">Identifiant (4 caractères max)</label>
                <input type="text" id="id" name="id" maxlength="4" required
                       value="<?= esc($compte['id'] ?? '') ?>">
            </div>
            <div class="champ">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" maxlength="30" required
                       value="<?= esc($compte['nom'] ?? '') ?>">
            </div>
            <div class="champ">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" maxlength="30" required
                       value="<?= esc($compte['prenom'] ?? '') ?>">
            </div>
        </div>

        <div class="grille">
            <div class="champ">
                <label for="login">Login</label>
                <input type="text" id="login" name="login" maxlength="20" required
                       value="<?= esc($compte['login'] ?? '') ?>">
            </div>
            <div class="champ">
                <label for="mdp">
                    Mot de passe<?= $modification ? ' (laisser vide pour le conserver)' : '' ?>
                </label>
                <input type="password" id="mdp" name="mdp" <?= $modification ? '' : 'required' ?>>
            </div>
            <div class="champ">
                <label for="dateEmbauche">Date d'embauche</label>
                <input type="date" id="dateEmbauche" name="dateEmbauche" required
                       value="<?= esc($compte['dateEmbauche'] ?? '') ?>">
            </div>
        </div>

        <div class="grille">
            <div class="champ">
                <label for="adresse">Adresse</label>
                <input type="text" id="adresse" name="adresse" maxlength="100" required
                       value="<?= esc($compte['adresse'] ?? '') ?>">
            </div>
            <div class="champ">
                <label for="cp">Code postal</label>
                <input type="text" id="cp" name="cp" maxlength="5" pattern="[0-9]{5}" required
                       value="<?= esc($compte['cp'] ?? '') ?>">
            </div>
            <div class="champ">
                <label for="ville">Ville</label>
                <input type="text" id="ville" name="ville" maxlength="50" required
                       value="<?= esc($compte['ville'] ?? '') ?>">
            </div>
        </div>

        <button type="submit"><?= $modification ? 'Enregistrer les modifications' : 'Créer le compte' ?></button>

        <?php if ($modification) : ?>
            <a class="bouton bouton-secondaire" href="<?= base_url('getdata?action=admin&type=' . $type) ?>">Annuler</a>
        <?php endif; ?>
    </form>
</div>

<div class="carte">
    <h2><?= esc($types[$type]) ?> — <?= count($comptes) ?> compte<?= count($comptes) > 1 ? 's' : '' ?></h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Login</th>
                <th>Ville</th>
                <th>Embauche</th>
                <th class="nombre">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($comptes as $c) : ?>
                <tr>
                    <td><?= esc($c['id']) ?></td>
                    <td><?= esc($c['nom']) ?></td>
                    <td><?= esc($c['prenom']) ?></td>
                    <td><?= esc($c['login']) ?></td>
                    <td><?= esc($c['ville']) ?></td>
                    <td><?= esc((new DateTime($c['dateEmbauche']))->format('d/m/Y')) ?></td>
                    <td class="nombre">
                        <a class="bouton bouton-discret"
                           href="<?= base_url('getdata?action=adminFormulaire&type=' . $type . '&compte=' . urlencode($c['id'])) ?>">
                            Modifier
                        </a>

                        <form method="post" action="<?= base_url('postdata') ?>" style="display:inline"
                              onsubmit="return confirm('Supprimer définitivement ce compte<?= $type === 'visiteur' ? ' ainsi que toutes ses fiches de frais' : '' ?> ?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="adminSupprimer">
                            <input type="hidden" name="type" value="<?= esc($type) ?>">
                            <input type="hidden" name="compte" value="<?= esc($c['id']) ?>">
                            <button type="submit" class="bouton-discret">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($comptes)) : ?>
                <tr><td colspan="7" class="vide">Aucun compte de ce type.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= view('templates/pied') ?>
