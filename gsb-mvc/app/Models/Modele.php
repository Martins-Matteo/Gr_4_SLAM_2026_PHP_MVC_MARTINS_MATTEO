<?php
// acces au Modele parent pour l'heritage
namespace App\Models;

use CodeIgniter\Model;

/**
 * Modele de l'application GSB - Gestion des frais.
 * Le modele contient TOUTES les requetes SQL de l'application :
 * aucune requete ne doit se trouver dans le controleur ni dans les vues.
 */
class Modele extends Model
{
    // =================================================================
    // AUTHENTIFICATION
    // =================================================================

    /**
     * Recherche un utilisateur a partir de son login dans les trois tables
     * (Visiteur, Comptable, Administrateur) et renvoie ses informations
     * ainsi que son role, ou null si le login n'existe pas.
     */
    public function getUtilisateurParLogin($login)
    {
        $db = db_connect();

        $tables = [
            'Visiteur'       => 'visiteur',
            'Comptable'      => 'comptable',
            'Administrateur' => 'administrateur',
        ];

        foreach ($tables as $table => $role) {
            $sql = 'SELECT id, nom, prenom, login, mdp FROM ' . $table . ' WHERE login = ?';
            $resultat = $db->query($sql, [$login])->getResultArray();

            if (count($resultat) === 1) {
                $utilisateur = $resultat[0];
                $utilisateur['role'] = $role;
                return $utilisateur;
            }
        }

        return null;
    }

    // =================================================================
    // FICHES DE FRAIS
    // =================================================================

    /**
     * Renvoie la fiche de frais d'un visiteur pour un mois donne
     * (avec le libelle de son etat), ou null si elle n'existe pas.
     */
    public function getFicheFrais($idVisiteur, $mois)
    {
        $db = db_connect();

        $sql = 'SELECT f.idVisiteur, f.mois, f.nbJustificatifs, f.montantValide,
                       f.dateModif, f.idEtat, e.libelle AS libelleEtat
                FROM FicheFrais AS f
                JOIN Etat AS e ON e.id = f.idEtat
                WHERE f.idVisiteur = ? AND f.mois = ?';

        $resultat = $db->query($sql, [$idVisiteur, $mois])->getResultArray();

        return count($resultat) === 1 ? $resultat[0] : null;
    }

    /**
     * Renvoie la liste des mois pour lesquels le visiteur possede une fiche,
     * du plus recent au plus ancien.
     */
    public function getMoisDisponibles($idVisiteur)
    {
        $db = db_connect();

        $sql = 'SELECT mois FROM FicheFrais WHERE idVisiteur = ? ORDER BY mois DESC';

        return $db->query($sql, [$idVisiteur])->getResultArray();
    }

    /**
     * Cree la fiche de frais du mois (etat "CR" : saisie en cours) ainsi que
     * les lignes de frais forfaitises correspondantes, toutes a zero.
     */
    public function creerFicheFrais($idVisiteur, $mois)
    {
        $db = db_connect();

        $db->transStart();

        $sql = 'INSERT INTO FicheFrais (idVisiteur, mois, nbJustificatifs, montantValide, dateModif, idEtat)
                VALUES (?, ?, 0, 0.00, CURDATE(), ?)';
        $db->query($sql, [$idVisiteur, $mois, 'CR']);

        $sql = 'INSERT INTO LigneFraisForfait (idVisiteur, mois, idFraisForfait, quantite)
                SELECT ?, ?, id, 0 FROM FraisForfait';
        $db->query($sql, [$idVisiteur, $mois]);

        $db->transComplete();

        return $db->transStatus();
    }

    // =================================================================
    // FRAIS FORFAITISES
    // =================================================================

    /**
     * Renvoie la liste des frais forfaitises (code, libelle, montant unitaire).
     */
    public function getFraisForfait()
    {
        $db = db_connect();

        $sql = 'SELECT id, libelle, montant FROM FraisForfait ORDER BY libelle';

        return $db->query($sql)->getResultArray();
    }

    /**
     * Renvoie les lignes de frais forfaitises d'une fiche : quantite saisie,
     * montant unitaire et total de la ligne.
     */
    public function getLignesForfait($idVisiteur, $mois)
    {
        $db = db_connect();

        $sql = 'SELECT l.idFraisForfait, ff.libelle, ff.montant, l.quantite,
                       (ff.montant * l.quantite) AS total
                FROM LigneFraisForfait AS l
                JOIN FraisForfait AS ff ON ff.id = l.idFraisForfait
                WHERE l.idVisiteur = ? AND l.mois = ?
                ORDER BY ff.libelle';

        return $db->query($sql, [$idVisiteur, $mois])->getResultArray();
    }

    /**
     * Met a jour la quantite d'un frais forfaitise et la date de modification
     * de la fiche.
     */
    public function majQuantiteForfait($idVisiteur, $mois, $idFraisForfait, $quantite)
    {
        $db = db_connect();

        $sql = 'UPDATE LigneFraisForfait SET quantite = ?
                WHERE idVisiteur = ? AND mois = ? AND idFraisForfait = ?';
        $db->query($sql, [$quantite, $idVisiteur, $mois, $idFraisForfait]);

        return $db->affectedRows();
    }

    // =================================================================
    // FRAIS HORS FORFAIT
    // =================================================================

    /**
     * Renvoie les frais hors forfait d'une fiche, du plus ancien au plus recent.
     */
    public function getLignesHorsForfait($idVisiteur, $mois)
    {
        $db = db_connect();

        $sql = 'SELECT id, libelle, date, montant
                FROM LigneFraisHorsForfait
                WHERE idVisiteur = ? AND mois = ?
                ORDER BY date';

        return $db->query($sql, [$idVisiteur, $mois])->getResultArray();
    }

    /**
     * Ajoute un frais hors forfait a la fiche du mois.
     */
    public function ajouterHorsForfait($idVisiteur, $mois, $libelle, $date, $montant)
    {
        $db = db_connect();

        $sql = 'INSERT INTO LigneFraisHorsForfait (idVisiteur, mois, libelle, date, montant)
                VALUES (?, ?, ?, ?, ?)';
        $db->query($sql, [$idVisiteur, $mois, $libelle, $date, $montant]);

        return $db->insertID();
    }

    /**
     * Supprime un frais hors forfait, en verifiant qu'il appartient bien
     * au visiteur connecte.
     */
    public function supprimerHorsForfait($id, $idVisiteur)
    {
        $db = db_connect();

        $sql = 'DELETE FROM LigneFraisHorsForfait WHERE id = ? AND idVisiteur = ?';
        $db->query($sql, [$id, $idVisiteur]);

        return $db->affectedRows();
    }

    /**
     * Met a jour la date de modification et le nombre de justificatifs
     * (nombre de frais hors forfait) de la fiche.
     */
    public function majFiche($idVisiteur, $mois)
    {
        $db = db_connect();

        $sql = 'UPDATE FicheFrais
                SET dateModif = CURDATE(),
                    nbJustificatifs = (SELECT COUNT(*) FROM LigneFraisHorsForfait
                                       WHERE idVisiteur = ? AND mois = ?)
                WHERE idVisiteur = ? AND mois = ?';

        $db->query($sql, [$idVisiteur, $mois, $idVisiteur, $mois]);

        return $db->affectedRows();
    }

    // =================================================================
    // SUIVI DES FICHES (ESPACE COMPTABLE)
    // =================================================================

    /**
     * Renvoie la liste des etats possibles d'une fiche de frais.
     */
    public function getEtats()
    {
        $db = db_connect();

        $sql = 'SELECT id, libelle FROM Etat ORDER BY libelle';

        return $db->query($sql)->getResultArray();
    }

    /**
     * Renvoie la liste des visiteurs medicaux (pour le filtre du comptable).
     */
    public function getVisiteurs()
    {
        $db = db_connect();

        $sql = 'SELECT id, nom, prenom FROM Visiteur ORDER BY nom, prenom';

        return $db->query($sql)->getResultArray();
    }

    /**
     * Renvoie la liste des fiches de frais de tous les visiteurs, avec le
     * montant total calcule (forfait + hors forfait).
     * Les deux filtres sont facultatifs : chaine vide = pas de filtre.
     */
    public function getFiches($idVisiteur = '', $idEtat = '')
    {
        $db = db_connect();

        $sql = 'SELECT f.idVisiteur, f.mois, f.nbJustificatifs, f.montantValide,
                       f.dateModif, f.idEtat, e.libelle AS libelleEtat,
                       v.nom, v.prenom,
                       COALESCE((SELECT SUM(ff.montant * l.quantite)
                                 FROM LigneFraisForfait AS l
                                 JOIN FraisForfait AS ff ON ff.id = l.idFraisForfait
                                 WHERE l.idVisiteur = f.idVisiteur AND l.mois = f.mois), 0)
                     + COALESCE((SELECT SUM(h.montant)
                                 FROM LigneFraisHorsForfait AS h
                                 WHERE h.idVisiteur = f.idVisiteur AND h.mois = f.mois), 0)
                       AS montantTotal
                FROM FicheFrais AS f
                JOIN Etat AS e ON e.id = f.idEtat
                JOIN Visiteur AS v ON v.id = f.idVisiteur
                WHERE (? = \'\' OR f.idVisiteur = ?)
                  AND (? = \'\' OR f.idEtat = ?)
                ORDER BY f.mois DESC, v.nom, v.prenom';

        return $db->query($sql, [$idVisiteur, $idVisiteur, $idEtat, $idEtat])->getResultArray();
    }

    /**
     * Renvoie l'identite d'un visiteur.
     */
    public function getVisiteur($idVisiteur)
    {
        $db = db_connect();

        $sql = 'SELECT id, nom, prenom, login, ville FROM Visiteur WHERE id = ?';
        $resultat = $db->query($sql, [$idVisiteur])->getResultArray();

        return count($resultat) === 1 ? $resultat[0] : null;
    }

    /**
     * Change l'etat d'une fiche de frais et enregistre le montant valide
     * ainsi que le nombre de justificatifs retenus.
     */
    public function majEtatFiche($idVisiteur, $mois, $idEtat, $montantValide, $nbJustificatifs)
    {
        $db = db_connect();

        $sql = 'UPDATE FicheFrais
                SET idEtat = ?, montantValide = ?, nbJustificatifs = ?, dateModif = CURDATE()
                WHERE idVisiteur = ? AND mois = ?';

        $db->query($sql, [$idEtat, $montantValide, $nbJustificatifs, $idVisiteur, $mois]);

        return $db->affectedRows();
    }

    /**
     * Change uniquement l'etat d'une fiche (cloture par le visiteur,
     * mise en remboursement par le comptable).
     */
    public function changerEtat($idVisiteur, $mois, $idEtat)
    {
        $db = db_connect();

        $sql = 'UPDATE FicheFrais SET idEtat = ?, dateModif = CURDATE()
                WHERE idVisiteur = ? AND mois = ?';

        $db->query($sql, [$idEtat, $idVisiteur, $mois]);

        return $db->affectedRows();
    }
}
