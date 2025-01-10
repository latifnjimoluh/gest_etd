<?php
namespace App;

class Utilisateur {
    private $nom;
    private $prenom;
    private $email;
    private $mot_de_passe;
    private $role;

    public function __construct($nom, $prenom, $email, $mot_de_passe) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->mot_de_passe = $mot_de_passe;
        $this->role = 'admin';  // Rôle par défaut
    }

        // Getter pour l'email
        public function getEmail() {
            return $this->email;
        }
    
        // Getter pour le nom
        public function getNom() {
            return $this->nom;
        }
    
        // Getter pour le prénom
        public function getPrenom() {
            return $this->prenom;
        }
    
        // Getter pour le rôle
        public function getRole() {
            return $this->role;
        }
    
    // Vérifier si l'email existe déjà
    public static function emailExiste($email) {
        $pdo = \App\DBConnection::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() ? true : false;
    }
    
    // Méthode pour valider les champs
    public function valider($mot_de_passe_confirmation) {
        if (empty($this->nom) || empty($this->prenom) || empty($this->email) || empty($this->mot_de_passe)) {
            return "Tous les champs sont requis!";
        }
        if ($this->mot_de_passe !== $mot_de_passe_confirmation) {
            return "Les mots de passe ne correspondent pas!";
        }
        if (self::emailExiste($this->email)) {
            return "Cet email est déjà utilisé!";
        }
        return null; // Si pas d'erreur
    }

    // Méthode pour hacher le mot de passe
    public function hacherMotDePasse() {
        return password_hash($this->mot_de_passe, PASSWORD_BCRYPT);
    }

    // Méthode pour enregistrer l'utilisateur dans la base de données
    public function enregistrer() {
        $pdo = \App\DBConnection::getConnection();
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, role, mot_de_passe) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$this->nom, $this->prenom, $this->email, $this->role, $this->hacherMotDePasse()]);
        return $pdo->lastInsertId();
    }

     // Méthode pour vérifier les informations de connexion de l'utilisateur
     public static function verifierConnexion($email, $mot_de_passe) {
        $pdo = \App\DBConnection::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $utilisateur = $stmt->fetch();

        if ($utilisateur) {
            // Vérifier si le mot de passe correspond
            if (password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
                return $utilisateur; // Retourner les données de l'utilisateur
            } else {
                return null; // Mot de passe incorrect
            }
        } else {
            return null; // Utilisateur non trouvé
        }
    }
}
?>
