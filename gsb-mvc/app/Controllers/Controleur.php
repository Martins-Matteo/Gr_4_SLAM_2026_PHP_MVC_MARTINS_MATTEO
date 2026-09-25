<?php
// acces au controller parent pour l'heritage
namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * Controleur de l'application GSB - Gestion des frais.
 *
 * - la fonction index() est le CONTROLEUR FRONTAL : elle analyse l'etat du
 *   site (session, action demandee) et decide de la fonction a executer ;
 * - les autres fonctions publiques forment le controleur principal : elles
 *   appellent le Modele puis chargent la vue correspondante.
 */
class Controleur extends BaseController
{
    // =================================================================
    // CONTROLEUR FRONTAL
    // =================================================================
    public function index()
    {
        $session = session();

        // action demandee : par un formulaire (POST) ou par un lien (GET)
        $action = $this->request->getPost('action') ?? $this->request->getGet('action') ?? '';

        // ---------- pages accessibles sans etre connecte ----------
        if ($action === 'connexion') {
            return $this->connexion();
        }

        if ($action === 'mentions') {
            return $this->mentions();
        }

        // ---------- utilisateur non connecte : formulaire de connexion ----------
        if (! $session->get('id')) {
            return $this->formulaireConnexion();
        }

        // ---------- utilisateur connecte ----------
        $role = $session->get('role');

        if ($action === 'deconnexion') {
            return $this->deconnexion();
        }

        // ---------- actions reservees au visiteur medical ----------
        $actionsVisiteur = [
            'accueil', 'consulter', 'saisir', 'enregistrerForfait',
            'ajouterHorsForfait', 'supprimerHorsForfait', 'cloturerFiche',
        ];

        // ---------- actions reservees au comptable ----------
        $actionsComptable = ['comptable', 'comptableFiche', 'validerFiche', 'rembourserFiche'];

        if (in_array($action, $actionsVisiteur, true) && $role !== 'visiteur') {
            return $this->accesRefuse();
        }

        if (in_array($action, $actionsComptable, true) && $role !== 'comptable') {
            return $this->accesRefuse();
        }

        switch ($action) {
            // --- visiteur medical ---
            case 'consulter':
                return $this->consulter();

            case 'saisir':
                return $this->saisir();

            case 'enregistrerForfait':
                return $this->enregistrerForfait();

            case 'ajouterHorsForfait':
                return $this->ajouterHorsForfait();

            case 'supprimerHorsForfait':
                return $this->supprimerHorsForfait();

            case 'cloturerFiche':
                return $this->cloturerFiche();

            // --- comptable ---
            case 'comptable':
                return $this->comptable();

            case 'comptableFiche':
                return $this->comptableFiche();

            case 'validerFiche':
                return $this->validerFiche();

            case 'rembourserFiche':
                return $this->rembourserFiche();

            // --- page d'accueil selon le role ---
            default:
                if ($role === 'comptable') {
                    return $this->comptable();
                }

                if ($role === 'administrateur') {
                    return $this->accesRefuse("L'espace administrateur n'est pas encore disponible dans cette version.");
                }

                return $this->accueil();
        }
    }
    // ---------------------------
    // fin du controleur frontal
    // ---------------------------

    // =================================================================
    // CONNEXION / DECONNEXION
    // =================================================================

    /**
     * Affiche le formulaire de connexion.
     */
    public function formulaireConnexion($erreur = '')
    {
        $data['erreur'] = $erreur;

        return view('vueConnexion', $data);
    }

    /**
     * Verifie les identifiants saisis et ouvre la session.
     */
    public function connexion()
    {
        $login = trim((string) $this->request->getPost('login'));
        $mdp   = (string) $this->request->getPost('mdp');

        // controle de saisie : champs obligatoires
        if ($login === '' || $mdp === '') {
            return $this->formulaireConnexion('Le login et le mot de passe sont obligatoires.');
        }

        // acces au modele
        $Modele = new \App\Models\Modele();

        // appel d'une fonction du modele
        $utilisateur = $Modele->getUtilisateurParLogin($login);

        // identifiants incorrects
        if ($utilisateur === null || ! $this->motDePasseValide($mdp, $utilisateur['mdp'])) {
            return $this->formulaireConnexion('Identifiants incorrects.');
        }

        // ouverture de la session
        session()->set([
            'id'     => $utilisateur['id'],
            'nom'    => $utilisateur['nom'],
            'prenom' => $utilisateur['prenom'],
            'role'   => $utilisateur['role'],
        ]);

        // chaque profil arrive sur son propre espace
        if ($utilisateur['role'] === 'comptable') {
            return $this->comptable();
        }

        if ($utilisateur['role'] === 'administrateur') {
            return $this->accesRefuse("L'espace administrateur n'est pas encore disponible dans cette version.");
        }

        return $this->accueil();
    }

    /**
     * Compare le mot de passe saisi avec celui enregistre en base.
     * Les mots de passe sont normalement haches (password_hash) ; la
     * comparaison directe reste possible pour les anciens comptes de test.
     */
    private function motDePasseValide($mdpSaisi, $mdpEnBase)
    {
        if (password_verify($mdpSaisi, $mdpEnBase)) {
            return true;
        }

        return hash_equals($mdpEnBase, $mdpSaisi);
    }

    /**
     * Ferme la session et revient au formulaire de connexion.
     */
    public function deconnexion()
    {
        session()->destroy();

        return $this->formulaireConnexion();
    }

    // =================================================================
    // ACCUEIL
    // =================================================================

    /**
     * Affiche l'accueil du visiteur : etat et total de la fiche du mois choisi.
     */
    public function accueil()
    {
        $idVisiteur = session()->get('id');
        $mois       = $this->moisDemande();

        $Modele = new \App\Models\Modele();

        $donnees = [
            'fiche'         => $Modele->getFicheFrais($idVisiteur, $mois),
            'lignesForfait' => $Modele->getLignesForfait($idVisiteur, $mois),
            'lignesHors'    => $Modele->getLignesHorsForfait($idVisiteur, $mois),
            'moisListe'     => $Modele->getMoisDisponibles($idVisiteur),
        ];

        // jeu de donnees transmis a la vue
        $data['resultat'] = $donnees;
        $data['mois']     = $mois;
        $data['total']    = $this->calculerTotal($donnees['lignesForfait'], $donnees['lignesHors']);

        return view('vueAccueil', $data);
    }

    // =================================================================
    // CAS D'UTILISATION : CONSULTER FICHE DE FRAIS
    // =================================================================
    public function consulter()
    {
        $idVisiteur = session()->get('id');
        $mois       = $this->moisDemande();

        $Modele = new \App\Models\Modele();

        $donnees = [
            'fiche'         => $Modele->getFicheFrais($idVisiteur, $mois),
            'lignesForfait' => $Modele->getLignesForfait($idVisiteur, $mois),
            'lignesHors'    => $Modele->getLignesHorsForfait($idVisiteur, $mois),
            'moisListe'     => $Modele->getMoisDisponibles($idVisiteur),
        ];

        $data['resultat'] = $donnees;
        $data['mois']     = $mois;
        $data['total']    = $this->calculerTotal($donnees['lignesForfait'], $donnees['lignesHors']);

        return view('vueConsulter', $data);
    }

    // =================================================================
    // CAS D'UTILISATION : SAISIR FICHE DE FRAIS
    // =================================================================

    /**
     * Affiche le formulaire de saisie du mois choisi.
     * La fiche du mois est creee automatiquement si elle n'existe pas encore.
     */
    public function saisir($message = '', $erreur = '')
    {
        $idVisiteur = session()->get('id');
        $mois       = $this->moisDemande();

        $Modele = new \App\Models\Modele();

        $fiche = $Modele->getFicheFrais($idVisiteur, $mois);

        if ($fiche === null) {
            $Modele->creerFicheFrais($idVisiteur, $mois);
            $fiche = $Modele->getFicheFrais($idVisiteur, $mois);
        }

        $donnees = [
            'fiche'         => $fiche,
            'lignesForfait' => $Modele->getLignesForfait($idVisiteur, $mois),
            'lignesHors'    => $Modele->getLignesHorsForfait($idVisiteur, $mois),
            'moisListe'     => $Modele->getMoisDisponibles($idVisiteur),
        ];

        $data['resultat'] = $donnees;
        $data['mois']     = $mois;
        $data['total']    = $this->calculerTotal($donnees['lignesForfait'], $donnees['lignesHors']);
        $data['message']  = $message;
        $data['erreur']   = $erreur;

        return view('vueSaisie', $data);
    }

    /**
     * Enregistre les quantites des frais forfaitises.
     */
    public function enregistrerForfait()
    {
        $idVisiteur = session()->get('id');
        $mois       = $this->moisDemande();

        $Modele = new \App\Models\Modele();

        // la fiche doit exister et etre encore modifiable (etat CR)
        $fiche = $Modele->getFicheFrais($idVisiteur, $mois);

        if ($fiche === null || $fiche['idEtat'] !== 'CR') {
            return $this->saisir('', "Cette fiche n'est plus modifiable.");
        }

        $quantites = $this->request->getPost('quantite');

        if (! is_array($quantites)) {
            $quantites = [];
        }

        // controle de saisie : entiers positifs ou nuls
        foreach ($quantites as $idFrais => $quantite) {
            if (! ctype_digit((string) $quantite)) {
                return $this->saisir('', 'Les quantités doivent être des nombres entiers positifs ou nuls.');
            }
        }

        foreach ($quantites as $idFrais => $quantite) {
            $Modele->majQuantiteForfait($idVisiteur, $mois, $idFrais, (int) $quantite);
        }

        $Modele->majFiche($idVisiteur, $mois);

        return $this->saisir('Les frais forfaitisés ont été enregistrés.');
    }

    /**
     * Ajoute un frais hors forfait apres controle des saisies.
     */
    public function ajouterHorsForfait()
    {
        $idVisiteur = session()->get('id');
        $mois       = $this->moisDemande();

        $libelle = trim((string) $this->request->getPost('libelle'));
        $date    = trim((string) $this->request->getPost('date'));
        $montant = str_replace(',', '.', trim((string) $this->request->getPost('montant')));

        $Modele = new \App\Models\Modele();

        $fiche = $Modele->getFicheFrais($idVisiteur, $mois);

        if ($fiche === null || $fiche['idEtat'] !== 'CR') {
            return $this->saisir('', "Cette fiche n'est plus modifiable.");
        }

        // controle de saisie : libelle
        if ($libelle === '' || mb_strlen($libelle) > 100) {
            return $this->saisir('', 'Le libellé est obligatoire (100 caractères maximum).');
        }

        // controle de saisie : date valide, ni dans le futur, ni vieille de plus d'un an
        $dateSaisie = \DateTime::createFromFormat('Y-m-d', $date);

        if ($dateSaisie === false
            || $dateSaisie > new \DateTime()
            || $dateSaisie < (new \DateTime())->modify('-1 year')) {
            return $this->saisir('', 'La date est invalide : elle doit dater de moins d\'un an et ne pas être dans le futur.');
        }

        // controle de saisie : montant numerique strictement positif
        if (! is_numeric($montant) || (float) $montant <= 0) {
            return $this->saisir('', 'Le montant doit être un nombre supérieur à 0.');
        }

        $Modele->ajouterHorsForfait($idVisiteur, $mois, $libelle, $date, (float) $montant);
        $Modele->majFiche($idVisiteur, $mois);

        return $this->saisir('Le frais hors forfait a été ajouté.');
    }

    /**
     * Supprime un frais hors forfait de la fiche en cours de saisie.
     */
    public function supprimerHorsForfait()
    {
        $idVisiteur = session()->get('id');
        $mois       = $this->moisDemande();
        $id         = (int) $this->request->getPost('id');

        $Modele = new \App\Models\Modele();

        $fiche = $Modele->getFicheFrais($idVisiteur, $mois);

        if ($fiche === null || $fiche['idEtat'] !== 'CR') {
            return $this->saisir('', "Cette fiche n'est plus modifiable.");
        }

        $nb = $Modele->supprimerHorsForfait($id, $idVisiteur);
        $Modele->majFiche($idVisiteur, $mois);

        if ($nb === 0) {
            return $this->saisir('', 'Ce frais hors forfait est introuvable.');
        }

        return $this->saisir('Le frais hors forfait a été supprimé.');
    }

    // =================================================================
    // CAS D'UTILISATION : CLOTURER SA FICHE (VISITEUR)
    // =================================================================

    /**
     * Le visiteur cloture sa fiche du mois : elle passe de l'etat
     * "saisie en cours" (CR) a l'etat "saisie cloturee" (CL) et n'est
     * plus modifiable. Elle devient alors visible du comptable.
     */
    public function cloturerFiche()
    {
        $idVisiteur = session()->get('id');
        $mois       = $this->moisDemande();

        $Modele = new \App\Models\Modele();

        $fiche = $Modele->getFicheFrais($idVisiteur, $mois);

        if ($fiche === null || $fiche['idEtat'] !== 'CR') {
            return $this->saisir('', 'Cette fiche ne peut plus être clôturée.');
        }

        $Modele->majFiche($idVisiteur, $mois);
        $Modele->changerEtat($idVisiteur, $mois, 'CL');

        return $this->saisir('La fiche a été clôturée et transmise au service comptable.');
    }

    // =================================================================
    // ESPACE COMPTABLE
    // =================================================================

    /**
     * Liste des fiches de frais de tous les visiteurs, avec filtres
     * par visiteur et par etat.
     */
    public function comptable($message = '', $erreur = '')
    {
        $Modele = new \App\Models\Modele();

        $idVisiteur = (string) ($this->request->getGet('visiteur') ?? '');
        $idEtat     = (string) ($this->request->getGet('etat') ?? '');

        $donnees = [
            'fiches'    => $Modele->getFiches($idVisiteur, $idEtat),
            'visiteurs' => $Modele->getVisiteurs(),
            'etats'     => $Modele->getEtats(),
        ];

        $data['resultat']   = $donnees;
        $data['idVisiteur'] = $idVisiteur;
        $data['idEtat']     = $idEtat;
        $data['message']    = $message;
        $data['erreur']     = $erreur;

        return view('vueComptable', $data);
    }

    /**
     * Detail d'une fiche de frais d'un visiteur, avec les actions
     * de validation et de mise en remboursement.
     */
    public function comptableFiche($message = '', $erreur = '')
    {
        $idVisiteur = (string) ($this->request->getPost('visiteur') ?? $this->request->getGet('visiteur') ?? '');
        $mois       = $this->moisDemande();

        $Modele = new \App\Models\Modele();

        $fiche = $Modele->getFicheFrais($idVisiteur, $mois);

        if ($fiche === null) {
            return $this->comptable('', 'Cette fiche de frais est introuvable.');
        }

        $donnees = [
            'visiteur'      => $Modele->getVisiteur($idVisiteur),
            'fiche'         => $fiche,
            'lignesForfait' => $Modele->getLignesForfait($idVisiteur, $mois),
            'lignesHors'    => $Modele->getLignesHorsForfait($idVisiteur, $mois),
        ];

        $data['resultat']   = $donnees;
        $data['idVisiteur'] = $idVisiteur;
        $data['mois']       = $mois;
        $data['total']      = $this->calculerTotal($donnees['lignesForfait'], $donnees['lignesHors']);
        $data['message']    = $message;
        $data['erreur']     = $erreur;

        return view('vueComptableFiche', $data);
    }

    /**
     * Validation d'une fiche cloturee : le comptable enregistre le montant
     * valide et le nombre de justificatifs retenus, la fiche passe a l'etat
     * "validee et mise en paiement" (VA).
     */
    public function validerFiche()
    {
        $idVisiteur = (string) $this->request->getPost('visiteur');
        $mois       = $this->moisDemande();

        $montant = str_replace(',', '.', trim((string) $this->request->getPost('montantValide')));
        $nbJust  = trim((string) $this->request->getPost('nbJustificatifs'));

        $Modele = new \App\Models\Modele();

        $fiche = $Modele->getFicheFrais($idVisiteur, $mois);

        if ($fiche === null || $fiche['idEtat'] !== 'CL') {
            return $this->comptableFiche('', 'Seule une fiche clôturée peut être validée.');
        }

        // controles de saisie
        if (! is_numeric($montant) || (float) $montant < 0) {
            return $this->comptableFiche('', 'Le montant validé doit être un nombre positif.');
        }

        if (! ctype_digit($nbJust)) {
            return $this->comptableFiche('', 'Le nombre de justificatifs doit être un entier positif ou nul.');
        }

        $Modele->majEtatFiche($idVisiteur, $mois, 'VA', (float) $montant, (int) $nbJust);

        return $this->comptableFiche('La fiche a été validée et mise en paiement.');
    }

    /**
     * Une fiche validee passe a l'etat "remboursee" (RB) une fois le
     * virement effectue.
     */
    public function rembourserFiche()
    {
        $idVisiteur = (string) $this->request->getPost('visiteur');
        $mois       = $this->moisDemande();

        $Modele = new \App\Models\Modele();

        $fiche = $Modele->getFicheFrais($idVisiteur, $mois);

        if ($fiche === null || $fiche['idEtat'] !== 'VA') {
            return $this->comptableFiche('', 'Seule une fiche validée peut être marquée comme remboursée.');
        }

        $Modele->changerEtat($idVisiteur, $mois, 'RB');

        return $this->comptableFiche('La fiche a été marquée comme remboursée.');
    }

    // =================================================================
    // PAGES DIVERSES
    // =================================================================
    public function mentions()
    {
        return view('vueMentions');
    }

    /**
     * Page affichee lorsqu'un utilisateur demande une action qui ne
     * correspond pas a son profil.
     */
    public function accesRefuse($message = "Cette page n'est pas accessible avec votre profil.")
    {
        $data['message'] = $message;

        return view('vueMessage', $data);
    }

    // =================================================================
    // FONCTIONS UTILITAIRES DU CONTROLEUR
    // =================================================================

    /**
     * Renvoie le mois demande (format AAAAMM) apres controle,
     * ou le mois courant si aucun mois valide n'est transmis.
     */
    private function moisDemande()
    {
        $mois = $this->request->getPost('mois') ?? $this->request->getGet('mois') ?? '';

        if (preg_match('/^[0-9]{4}(0[1-9]|1[0-2])$/', (string) $mois)) {
            return (string) $mois;
        }

        return date('Ym');
    }

    /**
     * Calcule le total d'une fiche : frais forfaitises + frais hors forfait.
     */
    private function calculerTotal($lignesForfait, $lignesHors)
    {
        $total = 0.0;

        foreach ($lignesForfait as $ligne) {
            $total += (float) $ligne['total'];
        }

        foreach ($lignesHors as $ligne) {
            $total += (float) $ligne['montant'];
        }

        return $total;
    }
}
