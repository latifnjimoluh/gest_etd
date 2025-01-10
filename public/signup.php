<?php
include 'includes/url.php';
include '../classes/DBConnection.php';
include '../classes/Utilisateur.php';
include '../controller/SignupController.php';

use App\Controller\SigninController;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);
    $mot_de_passe_confirmation = trim($_POST['mot_de_passe_confirmation']);

    $controller = new SigninController();
    $message = $controller->inscrireUtilisateur($nom, $prenom, $email, $mot_de_passe, $mot_de_passe_confirmation);

    // Si l'inscription est réussie, rediriger vers la page d'accueil
    if ($message === "Inscription réussie!") {
        // Démarrer la session
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['message'] = "Bienvenue, vous êtes inscrit avec succès!"; // Message de bienvenue

        echo json_encode(['status' => 'success', 'message' => 'Inscription réussie!']);
        exit; // Sortie ici pour éviter l'exécution de la partie suivante
    } else {
        echo json_encode(['status' => 'error', 'message' => $message]);
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="assets/css/signup.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>

    <!-- Inclure la barre de navigation -->
    <?php include('includes/navbar.php'); ?>

    <div class="container">
        <h2>Inscription</h2>

        <form id="signup-form" method="POST">
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" required>
            <div id="nom-error"></div>

            <label for="prenom">Prénom</label>
            <input type="text" id="prenom" name="prenom" required>
            <div id="prenom-error"></div>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
            <div id="email-error" ></div>

            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            <div id="mot_de_passe-error"></div>

            <label for="mot_de_passe_confirmation">Confirmation du mot de passe</label>
            <input type="password" id="mot_de_passe_confirmation" name="mot_de_passe_confirmation" required>
            <div id="mot_de_passe_confirmation-error"></div>

            <button type="submit">S'inscrire</button>
        </form>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2025 Établissement Scolaire - Tous droits réservés</p>
    </div>

    <script src="assets/js/signup.js"></script>
</body>
</html>
