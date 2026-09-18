-- Utilisation de la base GSB
USE gsbV2;

-- Suppression des utilisateurs s'ils existent déjà (sécurité)
DROP USER IF EXISTS 'visiteur'@'localhost';
DROP USER IF EXISTS 'delegue'@'localhost';
DROP USER IF EXISTS 'comptable'@'localhost';
DROP USER IF EXISTS 'admin_gsb'@'localhost';

-- Création des utilisateurs
CREATE USER 'visiteur'@'localhost' IDENTIFIED BY 'visiteur123';
CREATE USER 'delegue'@'localhost' IDENTIFIED BY 'delegue123';
CREATE USER 'comptable'@'localhost' IDENTIFIED BY 'comptable123';
CREATE USER 'admin_gsb'@'localhost' IDENTIFIED BY 'admin123';

-- Droits du visiteur médical
GRANT SELECT, INSERT, UPDATE
ON gsbV2.*
TO 'visiteur'@'localhost';

-- Droits du délégué régional
GRANT SELECT, UPDATE
ON gsbV2.FicheFrais
TO 'delegue'@'localhost';

-- Droits du comptable
GRANT SELECT, UPDATE
ON gsbV2.FicheFrais
TO 'comptable'@'localhost';

-- Droits de l'administrateur (DSI)
GRANT ALL PRIVILEGES
ON gsbV2.*
TO 'administrateur'@'localhost';

-- Droits de l'administrateur (DSI)
GRANT ALL PRIVILEGES
ON gsbV2.*
TO 'admin_gsb'@'localhost';

-- Application des droits
FLUSH PRIVILEGES;
