<?php
session_start();
require 'config.php';

$errorMessage = "";

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Vérification en base de données : on essaie d'abord les visiteurs médicaux
    $stmt = $bdd->prepare("SELECT * FROM Visiteur WHERE login = ? AND mdp = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['id'] = $user['id'];
        $_SESSION['role'] = 'visiteur';

        header("Location: accueil_medical.php");
        exit();
    }

    // Si ce n'est pas un visiteur, on essaie les comptables
    $stmt = $bdd->prepare("SELECT * FROM Comptable WHERE login = ? AND mdp = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['id'] = $user['id'];
        $_SESSION['role'] = 'comptable';

        header("Location: compta.php");
        exit();
    }

    // Si ce n'est pas non plus un comptable, on essaie les administrateurs
    $stmt = $bdd->prepare("SELECT * FROM Administrateur WHERE login = ? AND mdp = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['id'] = $user['id'];
        $_SESSION['role'] = 'administrateur';

        header("Location: admin.php");
        exit();
    }

    $errorMessage = "Identifiants incorrects.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="index.css">
    <link href="/images/logo.ico" rel="shortcut icon" type="image/x-icon" />
</head>

<body>

    <div class="header">
        <img src="images/logo.png" alt="Logo GSB" class="logo">
        <h1>GSB</h1>
    </div>

    <div class="connexion">
        <h2>Connexion</h2>

        <!-- AFFICHAGE ERREUR -->
        <?php if ($errorMessage !== "") : ?>
            <p style="color:red;"><?= $errorMessage ?></p>
        <?php endif; ?>

        <form method="post">
            <label>Nom d'utilisateur :</label>
            <input type="text" name="username" required>

            <label>Mot de passe :</label>
            <input type="password" name="password" required>

            <button type="submit">Se connecter</button>
        </form>
    </div>

    <div class="footer">
        <p>&copy; 2026 GSB. Tous droits réservés.</p>
        <a href="mentions.php">Mentions légales</a>
    </div>

</body>
</html>
