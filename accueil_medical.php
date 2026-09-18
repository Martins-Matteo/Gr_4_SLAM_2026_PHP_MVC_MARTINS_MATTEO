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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link rel="stylesheet" href="accueil_medical.css">
    <link href="images/logo.ico" rel="shortcut icon" type="image/x-icon" />
  </head>
<?php

//$prenom = $_SESSION['prenom'] . ' ';
?>
  <body>
    <!-- Header -->
    <header class="header">
      <div class="header-left">
        <img src="images/logo.png" alt="Logo GSB" class="logo">
        <div>
          <p class="eyebrow">Accueil médicale</p>
          <h1>Bonjour, <?php echo htmlspecialchars($_SESSION['prenom'] ?? 'Utilisatrice'); ?></h1>
        </div>
      </div>
      <div class="header-actions">
        <a href="index.php" class="deconnexion">Déconnexion</a>
      </div>
    </header>

    <!-- Menu Navigation -->
    <div class="menu-dropdown">
      <button class="dropdown-btn" aria-expanded="false" aria-haspopup="true">☰ Menu</button>
      <div class="dropdown-content" role="menu">
        <a href="accueil_medical.php" class="active" role="menuitem">Accueil</a>
        <a href="frais.php" role="menuitem">Mes frais</a>
        <a href="saisi.php" role="menuitem">Saisie des frais</a>
      </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Consultation Section -->
      <div class="consultation_frais">
        <div class="mois">
          <label for="mois-select">Période :</label>
          <select id="mois-select" aria-label="Sélectionner une période">
            <option value="">-- Sélectionner mois/année --</option>
            <option value="Janv 2026">Janv 2026</option>
            <option value="Fév 2026">Fév 2026</option>
            <option value="Mars 2026">Mars 2026</option>
            <option value="Avr 2026">Avr 2026</option>
            <option value="Mai 2026">Mai 2026</option>
            <option value="Juin 2026">Juin 2026</option>
            <option value="Juil 2026">Juil 2026</option>
            <option value="Août 2026">Août 2026</option>
            <option value="Sept 2026">Sept 2026</option>
            <option value="Oct 2026">Oct 2026</option>
            <option value="Nov 2026">Nov 2026</option>
            <option value="Déc 2026">Déc 2026</option>
          </select>
        </div>
        
        <div class="etat">
          <label>État :</label>
          <div class="etat-static" id="etat-display">En cours</div>
        </div>
        
        <div class="total_frais">
          <label>Total des frais :</label>
          <span id="total-frais" class="total-amount">0 €</span>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="footer">
      <p>&copy; 2026 GSB. Tous droits réservés.</p>
      <a href="mentions.php">Mentions légales</a>
    </div>

    <script src="accueil_medical.js"></script>
  </body>
</html>