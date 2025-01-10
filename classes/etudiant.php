<?php
namespace App;

use App\DBConnection;

class Etudiant {

    private $nom;
    private $prenom;
    private $email;
    private $classe_id;
    private $matricule;
    private $email_parent;
    private $pdo;

    public function __construct($nom, $prenom, $email, $classe_id, $matricule, $email_parent) {
        $this->pdo = DBConnection::getConnection();
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->classe_id = $classe_id;
        $this->matricule = $matricule;
        $this->email_parent = $email_parent;
    }

    // Vérifier si l'email existe déjà
    public function emailExiste() {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$this->email]);
        return $stmt->fetch() ? true : false;
    }

    // Insérer l'utilisateur dans la table `utilisateurs`
    public function ajouterUtilisateur() {
        $password = password_hash('123', PASSWORD_DEFAULT); // Mot de passe par défaut
        $stmt = $this->pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, role, mot_de_passe) VALUES (?, ?, ?, 'etudiant', ?)");
        $stmt->execute([$this->nom, $this->prenom, $this->email, $password]);
    }

    // Insérer l'étudiant dans la table `etudiants`
    public function ajouterEtudiant() {
        $stmt = $this->pdo->prepare("INSERT INTO etudiants (matricule, classe_id, email_parent) VALUES (?, ?, ?)");
        $stmt->execute([$this->matricule, $this->classe_id, $this->email_parent]);
    }

    // Ajouter l'utilisateur et l'étudiant dans la base de données
    public function ajouter() {
        if ($this->emailExiste()) {
            throw new \Exception('Erreur: Cet email est déjà utilisé.');
        }

        // Ajouter l'utilisateur et l'étudiant
        $this->ajouterUtilisateur();
        $this->ajouterEtudiant();
    }

    // Fonction pour générer le matricule
    public static function generateMatricule($nom, $prenom, $classePrefix) {
        $nomPrefix = strtoupper(substr($nom, 0, 2));
        $prenomPrefix = strtoupper(substr($prenom, 0, 2));
        $randomDigits = rand(100, 999);
        return "ETD{$classePrefix}{$nomPrefix}{$prenomPrefix}{$randomDigits}";
    }


    public function modifierEtudiant() {
        // Mettre à jour l'utilisateur dans la table `utilisateurs`
        $stmt = $this->pdo->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, email = ? WHERE email = ?");
        $stmt->execute([$this->nom, $this->prenom, $this->email, $this->email]);
    
        // Mettre à jour l'étudiant dans la table `etudiants`
        $stmt = $this->pdo->prepare("UPDATE etudiants SET classe_id = ?, matricule = ?, email_parent = ? WHERE matricule = ?");
        $stmt->execute([$this->classe_id, $this->matricule, $this->email_parent, $this->matricule]);
    }

    

    public function supprimerEtudiant() {
        // Supprimer l'étudiant de la table `etudiants`
        $stmt = $this->pdo->prepare("DELETE FROM etudiants WHERE matricule = ?");
        $stmt->execute([$this->matricule]);
    
        // Supprimer l'utilisateur de la table `utilisateurs`
        $stmt = $this->pdo->prepare("DELETE FROM utilisateurs WHERE email = ?");
        $stmt->execute([$this->email]);
    }
    
}
?>
