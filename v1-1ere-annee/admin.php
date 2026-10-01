<?php
session_start();
require 'config.php';

// Seuls les administrateurs connectés peuvent accéder à cette page
if (!isset($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'administrateur') {
    header("Location: index.php");
    exit();
}

// admin.php a besoin du droit DELETE (absent du compte MySQL "visiteur" utilisé par config.php),
// on se connecte donc avec le compte dédié "admin_gsb" qui a tous les droits (ALL PRIVILEGES).
$bdd = new PDO('mysql:host=localhost;dbname=gsbV2;charset=utf8', 'admin_gsb', 'admin123');

// Tables gérables depuis cette page (liste blanche : jamais de nom de table venant directement de l'utilisateur)
$typesAutorises = [
    'visiteur'       => 'Visiteur médical',
    'comptable'      => 'Comptable',
    'administrateur' => 'Administrateur',
];

// Nom réel de la table en base (respecte la casse définie dans le CREATE TABLE :
// Visiteur, Comptable, Administrateur). Sous MySQL/MariaDB sur Linux, les noms
// de table sont sensibles à la casse : utiliser directement $type/$table (en
// minuscules) dans les requêtes provoque une erreur "Table doesn't exist".
$tablesReelles = [
    'visiteur'       => 'Visiteur',
    'comptable'      => 'Comptable',
    'administrateur' => 'Administrateur',
];

$type = $_GET['type'] ?? 'visiteur';
if (!array_key_exists($type, $typesAutorises)) {
    $type = 'visiteur';
}

$message = "";
$erreur  = "";

$champs = ['id', 'nom', 'prenom', 'login', 'mdp', 'adresse', 'cp', 'ville', 'dateEmbauche'];

// ---------- TRAITEMENT DES ACTIONS (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action    = $_POST['action'] ?? '';
    $postType  = $_POST['type'] ?? 'visiteur';
    if (!array_key_exists($postType, $typesAutorises)) {
        $postType = 'visiteur';
    }
    $table = $postType;

    if ($action === 'create' || $action === 'update') {

        $id           = trim($_POST['id'] ?? '');
        $nom          = trim($_POST['nom'] ?? '');
        $prenom       = trim($_POST['prenom'] ?? '');
        $login        = trim($_POST['login'] ?? '');
        $mdp          = $_POST['mdp'] ?? '';
        $adresse      = trim($_POST['adresse'] ?? '');
        $cp           = trim($_POST['cp'] ?? '');
        $ville        = trim($_POST['ville'] ?? '');
        $dateEmbauche = trim($_POST['dateEmbauche'] ?? '');

        // Validation simple des champs obligatoires
        if ($id === '' || $nom === '' || $prenom === '' || $login === '' || $adresse === '' || $cp === '' || $ville === '' || $dateEmbauche === '') {
            $erreur = "Tous les champs sont obligatoires (sauf le mot de passe lors d'une modification si vous ne souhaitez pas le changer).";
        } elseif (strlen($id) > 4) {
            $erreur = "L'identifiant ne peut pas dépasser 4 caractères.";
        } elseif ($action === 'create' && $mdp === '') {
            $erreur = "Le mot de passe est obligatoire à la création.";
        } else {
            try {
                if ($action === 'create') {
                    $stmt = $bdd->prepare(
                        "INSERT INTO {$tablesReelles[$table]} (id, nom, prenom, login, mdp, adresse, cp, ville, dateEmbauche)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                    );
                    $stmt->execute([$id, $nom, $prenom, $login, $mdp, $adresse, $cp, $ville, $dateEmbauche]);
                    $message = "Utilisateur créé avec succès.";
                } else {
                    // Modification : id d'origine (avant modif éventuelle) transmis à part
                    $idOrigine = trim($_POST['id_origine'] ?? $id);

                    if ($mdp !== '') {
                        $stmt = $bdd->prepare(
                            "UPDATE {$tablesReelles[$table]} SET id=?, nom=?, prenom=?, login=?, mdp=?, adresse=?, cp=?, ville=?, dateEmbauche=? WHERE id=?"
                        );
                        $stmt->execute([$id, $nom, $prenom, $login, $mdp, $adresse, $cp, $ville, $dateEmbauche, $idOrigine]);
                    } else {
                        // Mot de passe laissé vide -> on ne le modifie pas
                        $stmt = $bdd->prepare(
                            "UPDATE {$tablesReelles[$table]} SET id=?, nom=?, prenom=?, login=?, adresse=?, cp=?, ville=?, dateEmbauche=? WHERE id=?"
                        );
                        $stmt->execute([$id, $nom, $prenom, $login, $adresse, $cp, $ville, $dateEmbauche, $idOrigine]);
                    }
                    $message = "Utilisateur modifié avec succès.";
                }
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $erreur = "Cet identifiant existe déjà pour ce type d'utilisateur.";
                } else {
                    $erreur = "Erreur lors de l'enregistrement : " . $e->getMessage();
                }
            }
        }

    } elseif ($action === 'delete') {

        $id = trim($_POST['id'] ?? '');

        // On empêche l'administrateur de se supprimer lui-même par erreur
        if ($table === 'administrateur' && $id === $_SESSION['id']) {
            $erreur = "Vous ne pouvez pas supprimer votre propre compte administrateur.";
        } elseif ($id !== '') {
            try {
                $bdd->beginTransaction();

                if ($table === 'visiteur') {
                    // Un visiteur peut avoir des fiches de frais liées (FicheFrais -> LigneFraisForfait / LigneFraisHorsForfait)
                    // On supprime la chaîne dans l'ordre pour respecter les clés étrangères.
                    $stmt = $bdd->prepare("DELETE FROM LigneFraisForfait WHERE idVisiteur = ?");
                    $stmt->execute([$id]);

                    $stmt = $bdd->prepare("DELETE FROM LigneFraisHorsForfait WHERE idVisiteur = ?");
                    $stmt->execute([$id]);

                    $stmt = $bdd->prepare("DELETE FROM FicheFrais WHERE idVisiteur = ?");
                    $stmt->execute([$id]);
                }

                $stmt = $bdd->prepare("DELETE FROM {$tablesReelles[$table]} WHERE id = ?");
                $stmt->execute([$id]);

                $bdd->commit();
                $message = "Utilisateur supprimé avec succès (ainsi que ses fiches de frais liées, le cas échéant).";
            } catch (PDOException $e) {
                $bdd->rollBack();
                if ($e->getCode() === '23000') {
                    // Violation de clé étrangère
                    $erreur = "Impossible de supprimer cet utilisateur : des données liées existent encore ailleurs dans la base.";
                } elseif ($e->getCode() === '42000') {
                    // Droits insuffisants sur le compte MySQL utilisé
                    $erreur = "Impossible de supprimer : le compte MySQL utilisé par cette page n'a pas le droit DELETE.";
                } else {
                    $erreur = "Erreur lors de la suppression : " . $e->getMessage();
                }
            }
        }
    }

    // On retient le type courant + message pour l'affichage après redirection (PRG)
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_error']   = $erreur;
    header("Location: admin.php?type=" . urlencode($table));
    exit();
}

// Récupération des messages flash après redirection
if (isset($_SESSION['flash_message']) || isset($_SESSION['flash_error'])) {
    $message = $_SESSION['flash_message'] ?? '';
    $erreur  = $_SESSION['flash_error'] ?? '';
    unset($_SESSION['flash_message'], $_SESSION['flash_error']);
}

// ---------- RÉCUPÉRATION DE L'UTILISATEUR À ÉDITER (GET) ----------
$userAEditer = null;
if (isset($_GET['edit'])) {
    $idAEditer = $_GET['edit'];
    $stmt = $bdd->prepare("SELECT * FROM {$tablesReelles[$type]} WHERE id = ?");
    $stmt->execute([$idAEditer]);
    $userAEditer = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ---------- LISTE DES UTILISATEURS DU TYPE SÉLECTIONNÉ ----------
$stmt = $bdd->query("SELECT id, nom, prenom, login, adresse, cp, ville, dateEmbauche FROM {$tablesReelles[$type]} ORDER BY nom, prenom");
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration des utilisateurs</title>
    <link rel="stylesheet" href="admin.css">
    <link href="images/logo.ico" rel="shortcut icon" type="image/x-icon" />
</head>
<body>

    <header class="header">
        <div class="header-left">
            <img src="images/logo.png" alt="Logo GSB" class="logo">
            <div>
                <p class="eyebrow">Administration</p>
                <h1>Bonjour, <?= htmlspecialchars($_SESSION['prenom'] ?? 'Administrateur') ?></h1>
            </div>
        </div>
        <div class="header-actions">
            <a href="index.php" class="deconnexion">Déconnexion</a>
        </div>
    </header>

    <div class="admin-content">

        <?php if ($message !== ""): ?>
            <p class="flash flash-success"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <?php if ($erreur !== ""): ?>
            <p class="flash flash-error"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>

        <!-- Sélecteur de type d'utilisateur -->
        <nav class="type-tabs">
            <?php foreach ($typesAutorises as $cle => $libelle): ?>
                <a href="admin.php?type=<?= urlencode($cle) ?>"
                   class="<?= $cle === $type ? 'active' : '' ?>">
                    <?= htmlspecialchars($libelle) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="admin-layout">

            <!-- Formulaire de création / modification -->
            <div class="form-panel">
                <h2><?= $userAEditer ? "Modifier l'utilisateur" : "Ajouter un utilisateur" ?></h2>
                <form method="post" class="user-form">
                    <input type="hidden" name="action" value="<?= $userAEditer ? 'update' : 'create' ?>">
                    <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
                    <?php if ($userAEditer): ?>
                        <input type="hidden" name="id_origine" value="<?= htmlspecialchars($userAEditer['id']) ?>">
                    <?php endif; ?>

                    <label>Identifiant (4 caractères max) :</label>
                    <input type="text" name="id" maxlength="4" required
                           value="<?= htmlspecialchars($userAEditer['id'] ?? '') ?>">

                    <label>Nom :</label>
                    <input type="text" name="nom" required
                           value="<?= htmlspecialchars($userAEditer['nom'] ?? '') ?>">

                    <label>Prénom :</label>
                    <input type="text" name="prenom" required
                           value="<?= htmlspecialchars($userAEditer['prenom'] ?? '') ?>">

                    <label>Login :</label>
                    <input type="text" name="login" required
                           value="<?= htmlspecialchars($userAEditer['login'] ?? '') ?>">

                    <label>Mot de passe <?= $userAEditer ? '(laisser vide pour ne pas le changer)' : '' ?> :</label>
                    <input type="password" name="mdp" <?= $userAEditer ? '' : 'required' ?>>

                    <label>Adresse :</label>
                    <input type="text" name="adresse" required
                           value="<?= htmlspecialchars($userAEditer['adresse'] ?? '') ?>">

                    <label>Code postal :</label>
                    <input type="text" name="cp" maxlength="5" required
                           value="<?= htmlspecialchars($userAEditer['cp'] ?? '') ?>">

                    <label>Ville :</label>
                    <input type="text" name="ville" required
                           value="<?= htmlspecialchars($userAEditer['ville'] ?? '') ?>">

                    <label>Date d'embauche :</label>
                    <input type="date" name="dateEmbauche" required
                           value="<?= htmlspecialchars($userAEditer['dateEmbauche'] ?? '') ?>">

                    <div class="form-actions">
                        <button type="submit"><?= $userAEditer ? 'Enregistrer les modifications' : 'Créer l’utilisateur' ?></button>
                        <?php if ($userAEditer): ?>
                            <a class="btn-annuler" href="admin.php?type=<?= urlencode($type) ?>">Annuler</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Liste des utilisateurs -->
            <div class="list-panel">
                <h2>Liste — <?= htmlspecialchars($typesAutorises[$type]) ?> (<?= count($utilisateurs) ?>)</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Login</th>
                                <th>Ville</th>
                                <th>Embauche</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($utilisateurs)): ?>
                                <tr class="empty-row">
                                    <td colspan="7" class="empty">Aucun utilisateur pour ce type.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($utilisateurs as $u): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($u['id']) ?></td>
                                        <td><?= htmlspecialchars($u['nom']) ?></td>
                                        <td><?= htmlspecialchars($u['prenom']) ?></td>
                                        <td><?= htmlspecialchars($u['login']) ?></td>
                                        <td><?= htmlspecialchars($u['ville']) ?></td>
                                        <td><?= htmlspecialchars($u['dateEmbauche']) ?></td>
                                        <td class="actions-cell">
                                            <a class="btn-edit" href="admin.php?type=<?= urlencode($type) ?>&edit=<?= urlencode($u['id']) ?>">Modifier</a>
                                            <form method="post" class="inline-form"
                                                  onsubmit="return confirm('<?= $type === 'visiteur' ? 'Supprimer définitivement cet utilisateur ainsi que TOUTES ses fiches de frais liées (irréversible) ?' : 'Supprimer définitivement cet utilisateur ?' ?>');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($u['id']) ?>">
                                                <button type="submit" class="btn-delete">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <div class="footer">
        <p>&copy; 2026 GSB. Tous droits réservés.</p>
        <a href="mentions.php">Mentions légales</a>
    </div>

</body>
</html>
