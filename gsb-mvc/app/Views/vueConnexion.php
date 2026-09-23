<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="<?= base_url('css/index.css') ?>">
    <link href="<?= base_url('images/logo.ico') ?>" rel="shortcut icon" type="image/x-icon" />
</head>

<body>

    <div class="header">
        <img src="<?= base_url('images/logo.png') ?>" alt="Logo GSB" class="logo">
        <h1>GSB</h1>
    </div>

    <div class="connexion">
        <h2>Connexion</h2>

        <?php if (! empty($erreur)) : ?>
            <p style="color:red;"><?= esc($erreur) ?></p>
        <?php endif; ?>

        <form method="post" action="<?= base_url('postdata') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="connexion">

            <label>Nom d'utilisateur :</label>
            <input type="text" name="login" maxlength="20" required>

            <label>Mot de passe :</label>
            <input type="password" name="mdp" required>

            <button type="submit">Se connecter</button>
        </form>
    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> GSB. Tous droits réservés.</p>
        <a href="<?= base_url('getdata?action=mentions') ?>">Mentions légales</a>
    </div>

</body>
</html>
