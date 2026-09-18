-- =====================================================================
--  GSB - Gestion des frais (Situation 5 - PHP MVC CodeIgniter 4)
--  Script de création de la base gsbV2 : tables, clés, types, contraintes
--  Auteur : Mattéo Martins - 2SIO SLAM
--  SGBD cible : MariaDB >= 10.4 / MySQL >= 8.0.16 (contraintes CHECK actives)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS gsbV2
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE gsbV2;

-- Suppression dans l'ordre inverse des dépendances (clés étrangères)
DROP TABLE IF EXISTS LigneFraisHorsForfait;
DROP TABLE IF EXISTS LigneFraisForfait;
DROP TABLE IF EXISTS FicheFrais;
DROP TABLE IF EXISTS Visiteur;
DROP TABLE IF EXISTS Comptable;
DROP TABLE IF EXISTS Administrateur;
DROP TABLE IF EXISTS Etat;
DROP TABLE IF EXISTS FraisForfait;

-- ---------------------------------------------------------------------
-- Tables de référence
-- ---------------------------------------------------------------------
CREATE TABLE FraisForfait (
  id       CHAR(3)       NOT NULL,
  libelle  VARCHAR(100)  NOT NULL,
  montant  DECIMAL(10,2) NOT NULL,
  CONSTRAINT pk_fraisforfait PRIMARY KEY (id),
  CONSTRAINT ck_fraisforfait_montant CHECK (montant >= 0)
) ENGINE=InnoDB;

CREATE TABLE Etat (
  id       CHAR(2)      NOT NULL,
  libelle  VARCHAR(30)  NOT NULL,
  CONSTRAINT pk_etat PRIMARY KEY (id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Utilisateurs (mdp en VARCHAR(255) pour stocker un hash password_hash())
-- ---------------------------------------------------------------------
CREATE TABLE Visiteur (
  id            VARCHAR(4)    NOT NULL,
  nom           VARCHAR(30)   NOT NULL,
  prenom        VARCHAR(30)   NOT NULL,
  login         VARCHAR(20)   NOT NULL,
  mdp           VARCHAR(255)  NOT NULL,
  adresse       VARCHAR(100)  NOT NULL,
  cp            CHAR(5)       NOT NULL,
  ville         VARCHAR(50)   NOT NULL,
  dateEmbauche  DATE          NOT NULL,
  CONSTRAINT pk_visiteur PRIMARY KEY (id),
  CONSTRAINT uq_visiteur_login UNIQUE (login),
  CONSTRAINT ck_visiteur_cp CHECK (cp REGEXP '^[0-9]{5}$')
) ENGINE=InnoDB;

CREATE TABLE Comptable (
  id            VARCHAR(4)    NOT NULL,
  nom           VARCHAR(30)   NOT NULL,
  prenom        VARCHAR(30)   NOT NULL,
  login         VARCHAR(20)   NOT NULL,
  mdp           VARCHAR(255)  NOT NULL,
  adresse       VARCHAR(100)  NOT NULL,
  cp            CHAR(5)       NOT NULL,
  ville         VARCHAR(50)   NOT NULL,
  dateEmbauche  DATE          NOT NULL,
  CONSTRAINT pk_comptable PRIMARY KEY (id),
  CONSTRAINT uq_comptable_login UNIQUE (login),
  CONSTRAINT ck_comptable_cp CHECK (cp REGEXP '^[0-9]{5}$')
) ENGINE=InnoDB;

CREATE TABLE Administrateur (
  id            VARCHAR(4)    NOT NULL,
  nom           VARCHAR(30)   NOT NULL,
  prenom        VARCHAR(30)   NOT NULL,
  login         VARCHAR(20)   NOT NULL,
  mdp           VARCHAR(255)  NOT NULL,
  adresse       VARCHAR(100)  NOT NULL,
  cp            CHAR(5)       NOT NULL,
  ville         VARCHAR(50)   NOT NULL,
  dateEmbauche  DATE          NOT NULL,
  CONSTRAINT pk_administrateur PRIMARY KEY (id),
  CONSTRAINT uq_administrateur_login UNIQUE (login),
  CONSTRAINT ck_administrateur_cp CHECK (cp REGEXP '^[0-9]{5}$')
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Fiches de frais et lignes
-- ---------------------------------------------------------------------
CREATE TABLE FicheFrais (
  idVisiteur       VARCHAR(4)     NOT NULL,
  mois             CHAR(6)        NOT NULL,              -- format AAAAMM
  nbJustificatifs  INT            NOT NULL DEFAULT 0,
  montantValide    DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  dateModif        DATE           NOT NULL,
  idEtat           CHAR(2)        NOT NULL DEFAULT 'CR',
  CONSTRAINT pk_fichefrais PRIMARY KEY (idVisiteur, mois),
  CONSTRAINT fk_fichefrais_visiteur FOREIGN KEY (idVisiteur)
    REFERENCES Visiteur(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_fichefrais_etat FOREIGN KEY (idEtat)
    REFERENCES Etat(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT ck_fichefrais_mois CHECK (mois REGEXP '^[0-9]{4}(0[1-9]|1[0-2])$'),
  CONSTRAINT ck_fichefrais_nbjustif CHECK (nbJustificatifs >= 0),
  CONSTRAINT ck_fichefrais_montant CHECK (montantValide >= 0)
) ENGINE=InnoDB;

CREATE TABLE LigneFraisForfait (
  idVisiteur      VARCHAR(4)  NOT NULL,
  mois            CHAR(6)     NOT NULL,
  idFraisForfait  CHAR(3)     NOT NULL,
  quantite        INT         NOT NULL DEFAULT 0,
  CONSTRAINT pk_lignefraisforfait PRIMARY KEY (idVisiteur, mois, idFraisForfait),
  CONSTRAINT fk_lff_fichefrais FOREIGN KEY (idVisiteur, mois)
    REFERENCES FicheFrais(idVisiteur, mois) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_lff_fraisforfait FOREIGN KEY (idFraisForfait)
    REFERENCES FraisForfait(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT ck_lff_quantite CHECK (quantite >= 0)
) ENGINE=InnoDB;

CREATE TABLE LigneFraisHorsForfait (
  id          INT            NOT NULL AUTO_INCREMENT,
  idVisiteur  VARCHAR(4)     NOT NULL,
  mois        CHAR(6)        NOT NULL,
  libelle     VARCHAR(100)   NOT NULL,
  date        DATE           NOT NULL,
  montant     DECIMAL(10,2)  NOT NULL,
  CONSTRAINT pk_lignefraishorsforfait PRIMARY KEY (id),
  CONSTRAINT fk_lfhf_fichefrais FOREIGN KEY (idVisiteur, mois)
    REFERENCES FicheFrais(idVisiteur, mois) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT ck_lfhf_montant CHECK (montant > 0),
  CONSTRAINT ck_lfhf_libelle CHECK (CHAR_LENGTH(TRIM(libelle)) > 0)
) ENGINE=InnoDB;

SHOW TABLES;
