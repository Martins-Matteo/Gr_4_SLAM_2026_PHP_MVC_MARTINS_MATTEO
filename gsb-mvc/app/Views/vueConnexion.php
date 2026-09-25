<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — GSB</title>
    <link rel="stylesheet" href="<?= base_url('css/gsb.css') ?>">
    <link rel="shortcut icon" href="<?= base_url('images/logo.ico') ?>" type="image/x-icon">
</head>
<body>

<div class="page-connexion">
    <div class="bloc-connexion">
        <img src="<?= base_url('images/logo.png') ?>" alt="Logo GSB">
        <h1>Galaxy Swiss Bourdin</h1>
        <p class="sous-titre">Gestion des frais des visiteurs médicaux</p>

        <?php if (! empty($erreur)) : ?>
            <div class="message message-erreur"><?= esc($erreur) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('postdata') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="connexion">

            <div class="champ">
                <label for="login">Identifiant</label>
                <input type="text" id="login" name="login" maxlength="20" required autofocus>
            </div>

            <div class="champ">
                <label for="mdp">Mot de passe</label>
                <input type="password" id="mdp" name="mdp" required>
            </div>

            <button type="submit">Se connecter</button>
        </form>
    </div>
</div>

<footer class="pied">
    <p>&copy; <?= date('Y') ?> Galaxy Swiss Bourdin.
       <a href="<?= base_url('getdata?action=mentions') ?>">Mentions légales</a></p>
</footer>

</body>
</html>
