<?php
namespace App\Controller;

use App\Utilisateur;

class LoginController {

    // Connexion de l'utilisateur
    public function connexionUtilisateur($email, $mot_de_passe): void {
        // Vérification de l'utilisateur avec la méthode statique
        $utilisateur = Utilisateur::verifierConnexion($email, $mot_de_passe);

        if ($utilisateur) {
            // Démarre la session et enregistre les informations de l'utilisateur
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user'] = [
                'nom' => $utilisateur['nom'],
                'prenom' => $utilisateur['prenom'],
                'email' => $utilisateur['email'],
                'role' => $utilisateur['role'],
            ];

            // Réponse JSON ou HTML pour la demande AJAX
            echo '<p class="success">Connexion réussie! Vous serez redirigé...</p>';
            exit;
        } else {
            // Si les informations sont incorrectes
            echo '<p class="error">Email ou mot de passe incorrect.</p>';
            exit;
        }
    }
}

?>
