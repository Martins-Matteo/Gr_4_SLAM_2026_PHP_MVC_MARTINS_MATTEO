-- =====================================================================
--  GSB - Comptes MySQL/MariaDB et droits (principe du moindre privilège)
--  Les mots de passe ci-dessous sont à remplacer sur le serveur de production.
-- =====================================================================
USE gsbV2;

DROP USER IF EXISTS 'gsb_app'@'localhost';
DROP USER IF EXISTS 'gsb_admin_app'@'localhost';
DROP USER IF EXISTS 'gsb_dba'@'localhost';

CREATE USER 'gsb_app'@'localhost'       IDENTIFIED BY 'AppGsb#2026!';
CREATE USER 'gsb_admin_app'@'localhost' IDENTIFIED BY 'AdmAppGsb#2026!';
CREATE USER 'gsb_dba'@'localhost'       IDENTIFIED BY 'DbaGsb#2026!';

-- 1) Compte applicatif "default" de CodeIgniter (visiteurs et comptables)
GRANT SELECT ON gsbV2.FraisForfait   TO 'gsb_app'@'localhost';
GRANT SELECT ON gsbV2.Etat           TO 'gsb_app'@'localhost';
GRANT SELECT ON gsbV2.Visiteur       TO 'gsb_app'@'localhost';
GRANT SELECT ON gsbV2.Comptable      TO 'gsb_app'@'localhost';
GRANT SELECT ON gsbV2.Administrateur TO 'gsb_app'@'localhost';
GRANT SELECT, INSERT, UPDATE ON gsbV2.FicheFrais TO 'gsb_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON gsbV2.LigneFraisForfait     TO 'gsb_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON gsbV2.LigneFraisHorsForfait TO 'gsb_app'@'localhost';

-- 2) Compte applicatif "admin" de CodeIgniter (page de gestion des utilisateurs)
GRANT SELECT, INSERT, UPDATE, DELETE ON gsbV2.Visiteur       TO 'gsb_admin_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON gsbV2.Comptable      TO 'gsb_admin_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON gsbV2.Administrateur TO 'gsb_admin_app'@'localhost';
GRANT SELECT, DELETE ON gsbV2.FicheFrais            TO 'gsb_admin_app'@'localhost';
GRANT SELECT, DELETE ON gsbV2.LigneFraisForfait     TO 'gsb_admin_app'@'localhost';
GRANT SELECT, DELETE ON gsbV2.LigneFraisHorsForfait TO 'gsb_admin_app'@'localhost';

-- 3) Compte d'administration de la base (DSI) : maintenance, sauvegardes, structure
--    Non utilisé par l'application.
GRANT ALL PRIVILEGES ON gsbV2.* TO 'gsb_dba'@'localhost' WITH GRANT OPTION;

FLUSH PRIVILEGES;
