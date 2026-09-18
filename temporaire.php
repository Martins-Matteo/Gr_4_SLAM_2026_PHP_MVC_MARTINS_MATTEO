<?php
session_start();
require 'config.php';

if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8">
    <title>Temporaire</title>
    <link rel="stylesheet" href="temporaire.css">
    <link href="images/logo.ico" rel="shortcut icon" type="image/x-icon" />
  </head>
  <body>
    <div class="header">
      <img src="images/logo.png" alt="Logo GSB" class="logo">
      <h1>Page Temporaire</h1>
      <a href="index.php" class="deconnexion">Déconnexion</a>
    </div>

    <div class="content">
      <h1>Qui etes vous?</h1>
      <a href="accueil_medical.php">Visiteur medical</a>
      <a href="compta.php">Comptable</a>
    </div>
  </body>
</html>