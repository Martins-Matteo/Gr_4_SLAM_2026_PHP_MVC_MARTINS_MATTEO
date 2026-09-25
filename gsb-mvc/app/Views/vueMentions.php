<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentions légales — GSB</title>
    <link rel="stylesheet" href="<?= base_url('css/gsb.css') ?>">
    <link rel="shortcut icon" href="<?= base_url('images/logo.ico') ?>" type="image/x-icon">
</head>
<body>

<header class="entete">
    <img src="<?= base_url('images/logo.png') ?>" alt="Logo GSB" class="logo">
    <p class="titre">Mentions légales</p>
    <div class="identite">
        <a href="<?= base_url('/') ?>" class="deconnexion">Retour à l'application</a>
    </div>
</header>

<main class="contenu">
    <div class="carte texte">
        <h2>Éditeur de l'application</h2>
        <p>
            Galaxy Swiss Bourdin (GSB)<br>
            Laboratoire pharmaceutique — Siège social : Philadelphie<br>
            Courriel : contact@swiss-galaxy.com
        </p>
        <p>
            L'application « Gestion des frais » est un outil interne, réservé aux visiteurs médicaux,
            aux comptables et aux administrateurs du laboratoire. Elle est hébergée sur les serveurs
            de la direction des systèmes d'information de GSB.
        </p>

        <h2>Accès à l'application</h2>
        <p>
            L'accès est réservé aux personnes disposant d'un compte. Chaque utilisateur est responsable
            de la confidentialité de ses identifiants. GSB s'efforce d'assurer la disponibilité du service,
            sous réserve des interruptions nécessaires à la maintenance.
        </p>

        <h2>Données personnelles</h2>
        <p>
            Conformément au Règlement général sur la protection des données (RGPD), les données
            enregistrées dans l'application sont limitées à ce qui est nécessaire au remboursement
            des frais professionnels : identité de l'utilisateur, coordonnées professionnelles,
            fiches de frais et justificatifs associés.
        </p>
        <p>
            Ces données sont conservées pendant la durée légale de conservation des pièces comptables.
            Elles ne sont accessibles qu'aux services habilités : le visiteur médical pour ses propres
            fiches, le service comptable pour le contrôle et la validation, la DSI pour la maintenance
            technique. Elles ne sont jamais transmises à des tiers.
        </p>
        <p>
            Chaque utilisateur dispose d'un droit d'accès, de rectification et d'effacement des données
            le concernant. Toute demande est à adresser au délégué à la protection des données de GSB.
        </p>

        <h2>Sécurité</h2>
        <p>
            Les mots de passe sont stockés sous forme hachée. Les accès à la base de données sont
            restreints par des comptes distincts selon les besoins de chaque profil. Les formulaires
            sont protégés contre les envois frauduleux (jeton CSRF) et les données affichées sont
            échappées afin de prévenir l'exécution de code indésirable.
        </p>

        <h2>Propriété intellectuelle</h2>
        <p>
            L'ensemble des contenus de cette application (textes, mises en page, éléments graphiques,
            logo) est la propriété de Galaxy Swiss Bourdin. Toute reproduction ou diffusion en dehors
            du cadre professionnel est interdite.
        </p>
    </div>

    <p><a class="bouton" href="<?= base_url('/') ?>">Retour à l'application</a></p>
</main>

<footer class="pied">
    <p>&copy; <?= date('Y') ?> Galaxy Swiss Bourdin — Application de gestion des frais.</p>
</footer>

</body>
</html>
