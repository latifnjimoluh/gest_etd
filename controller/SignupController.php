<?php
namespace App\Controller;

use App\Utilisateur;

class SigninController {

    // Inscrire l'utilisateur et gérer la logique
    public function inscrireUtilisateur($nom, $prenom, $email, $mot_de_passe, $mot_de_passe_confirmation) {
        // Vérifier si l'email existe déjà
        if (Utilisateur::emailExiste($email)) {
            return "L'email est déjà utilisé!";
        }

        // Créer une instance d'Utilisateur
        $utilisateur = new Utilisateur($nom, $prenom, $email, $mot_de_passe);

        // Valider les données
        $validationMessage = $utilisateur->valider($mot_de_passe_confirmation);
        if ($validationMessage) {
            return $validationMessage; // Retourne le message d'erreur de validation
        }

        // Enregistrer l'utilisateur
        $utilisateur->enregistrer();

        // Vérifier si la session est déjà démarrée
        if (session_status() == PHP_SESSION_NONE) {
            session_start();  // Démarrer la session si ce n'est pas déjà fait
        }

        // Stocker les informations de l'utilisateur dans la session
        $_SESSION['user'] = [
            'nom' => $utilisateur->getNom(), // Utiliser le getter
            'prenom' => $utilisateur->getPrenom(), // Utiliser le getter
            'email' => $utilisateur->getEmail(), // Utiliser le getter
            'role' => $utilisateur->getRole(), // Utiliser le getter
        ];

        return "Inscription réussie!";
    }
}
?>
