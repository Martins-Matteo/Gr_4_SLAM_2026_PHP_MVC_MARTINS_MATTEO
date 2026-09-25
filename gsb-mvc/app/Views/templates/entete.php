<?php
/**
 * En-tête commun à toutes les pages de l'application (hors connexion).
 * Attend deux variables facultatives :
 *   $titrePage  : titre affiché dans l'en-tête et l'onglet du navigateur
 *   $menuActif  : 'accueil', 'consulter', 'saisir', 'comptable' ou 'admin'
 */
$titrePage = $titrePage ?? 'Gestion des frais';
$menuActif = $menuActif ?? '';
$role      = session()->get('role') ?? '';
$moisMenu  = $mois ?? date('Ym');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titrePage) ?> — GSB</title>
    <link rel="stylesheet" href="<?= base_url('css/gsb.css') ?>">
    <link rel="shortcut icon" href="<?= base_url('images/logo.ico') ?>" type="image/x-icon">
</head>
<body>

<header class="entete">
    <img src="<?= base_url('images/logo.png') ?>" alt="Logo GSB" class="logo">
    <p class="titre"><?= esc($titrePage) ?></p>

    <div class="identite">
        <span>
            <?= esc(session()->get('prenom')) ?> <?= esc(session()->get('nom')) ?>
            <span class="role"><?= esc($role) ?></span>
        </span>
        <a href="<?= base_url('getdata?action=deconnexion') ?>" class="deconnexion">Déconnexion</a>
    </div>
</header>

<nav class="menu">
    <?php if ($role === 'visiteur') : ?>
        <a href="<?= base_url('/') ?>" class="<?= $menuActif === 'accueil' ? 'actif' : '' ?>">Accueil</a>
        <a href="<?= base_url('getdata?action=consulter&mois=' . $moisMenu) ?>" class="<?= $menuActif === 'consulter' ? 'actif' : '' ?>">Mes frais</a>
        <a href="<?= base_url('getdata?action=saisir&mois=' . $moisMenu) ?>" class="<?= $menuActif === 'saisir' ? 'actif' : '' ?>">Saisie des frais</a>
    <?php elseif ($role === 'comptable') : ?>
        <a href="<?= base_url('/') ?>" class="<?= $menuActif === 'comptable' ? 'actif' : '' ?>">Fiches de frais</a>
    <?php elseif ($role === 'administrateur') : ?>
        <a href="<?= base_url('/') ?>" class="<?= $menuActif === 'admin' ? 'actif' : '' ?>">Utilisateurs</a>
    <?php endif; ?>
</nav>

<main class="contenu">
