# GSB — Gestion des frais (PHP MVC, CodeIgniter 4)

Projet BTS SIO — option SLAM — Situation 5 — Groupe 4 — **Mattéo Martins**
Lycée Notre-Dame de la Paix, Ploemeur — année 2026-2027

Application web permettant aux visiteurs médicaux du laboratoire Galaxy Swiss Bourdin
de saisir et de consulter leurs fiches de frais, au service comptable de les valider
et de les rembourser, et à un administrateur de gérer les comptes.

Ce dépôt contient la **migration de l'application de 1ère année (PHP procédural) vers
une architecture MVC** avec le framework CodeIgniter 4.

---

## Contenu du dépôt

| Dossier | Contenu |
|---|---|
| `gsb-mvc/` | L'application migrée (CodeIgniter 4.6) — **version livrée** |
| `v1-1ere-annee/` | L'application de 1ère année en PHP procédural, conservée pour comparaison |
| `SQL/` | Scripts de la base `gsbV2` : création des tables, jeu d'essai, comptes et droits |
| `captures/` | Captures d'écran des deux versions (`v1/` et `v2/`) |

---

## Architecture

L'application respecte la structure imposée par le sujet :

- **3 routes seulement** (`app/Config/Routes.php`) — `/`, `postdata` (POST) et `getdata` (GET),
  qui aboutissent toutes à `Controleur::index()` ;
- **un contrôleur frontal** — `index()` lit l'action demandée, la session et le rôle,
  refuse l'action si elle ne correspond pas au profil, puis appelle la bonne fonction ;
- **un modèle unique** — `app/Models/Modele.php` regroupe toutes les requêtes SQL,
  une requête par fonction, en requêtes préparées ;
- **des vues d'affichage seul** — `app/Views/`, avec un en-tête, un menu adapté au profil
  et un pied de page communs (`app/Views/templates/`).

```
gsb-mvc/
├── app/
│   ├── Config/       Routes.php, Filters.php (CSRF), Database.php
│   ├── Controllers/  Controleur.php   → index() = contrôleur frontal
│   ├── Models/       Modele.php       → toutes les requêtes SQL
│   └── Views/        vueConnexion, vueAccueil, vueConsulter, vueSaisie,
│                     vueComptable, vueComptableFiche, vueAdmin,
│                     vueMessage, vueMentions + templates/
├── public/           index.php, .htaccess, css/gsb.css, images/   ← DocumentRoot
├── writable/         cache, logs, sessions
└── system/           noyau de CodeIgniter (non modifié)
```

## Les trois profils

| Profil | Ce qu'il peut faire |
|---|---|
| Visiteur médical | Saisir et consulter ses fiches, puis les clôturer |
| Comptable | Consulter toutes les fiches, les valider, les marquer remboursées |
| Administrateur | Créer, modifier et supprimer les comptes des trois profils |

Cycle de vie d'une fiche : **CR** (saisie en cours) → **CL** (clôturée) → **VA** (validée
et mise en paiement) → **RB** (remboursée).

---

## Installation

1. **Base de données** — exécuter dans l'ordre, avec un compte administrateur MySQL :

   ```sql
   SOURCE SQL/gsb_creation_tables.sql;     -- structure de gsbV2
   SOURCE SQL/Script_Insert.sql;           -- jeu d'essai
   SOURCE SQL/gsb_utilisateurs_droits.sql; -- comptes MySQL et droits
   ```

2. **Serveur web** — faire pointer le DocumentRoot Apache sur `gsb-mvc/public`
   et activer le module `rewrite`.

3. **Configuration** — copier `gsb-mvc/env` en `gsb-mvc/.env` et renseigner :

   ```
   CI_ENVIRONMENT = production
   app.baseURL = 'http://adresse-du-site/'

   database.default.database = gsbV2
   database.default.username = gsb_app
   database.default.password = ********

   database.admin.database  = gsbV2
   database.admin.username  = gsb_admin_app
   database.admin.password  = ********
   ```

4. **Droits** — `writable/` doit appartenir en écriture à l'utilisateur du serveur web :

   ```bash
   chown -R www-data:www-data gsb-mvc/writable
   chmod -R 775 gsb-mvc/writable
   ```

Le fichier `.env` n'est **pas** versionné : il contient les mots de passe de la base.

## Sécurité

- Mots de passe stockés hachés (`password_hash` / `password_verify`), jamais en clair.
- Requêtes préparées pour toutes les requêtes SQL.
- Protection CSRF de CodeIgniter active sur tous les formulaires.
- Sorties échappées avec `esc()`.
- **Deux comptes MySQL distincts** selon le principe du moindre privilège :
  `gsb_app` (aucun droit d'écriture sur les tables de comptes) pour l'application,
  `gsb_admin_app` pour le seul espace d'administration.
- Contrôle du profil avant chaque action, dans le contrôleur frontal.

## Recette

Les 40 tests du cahier de recette (17 sur la base de données, 23 fonctionnels) ont été
déroulés : tous sont conformes au résultat attendu. Le détail figure dans la partie 8 du
dossier examen.
