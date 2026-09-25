<?php
echo view('templates/entete', ['titrePage' => 'Information']);
?>

<div class="carte">
    <h2>Information</h2>
    <p><?= esc($message) ?></p>
    <p><a class="bouton" href="<?= base_url('/') ?>">Retour à l'accueil</a></p>
</div>

<?= view('templates/pied') ?>
