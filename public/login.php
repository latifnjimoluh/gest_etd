<?php
include 'includes/url.php'; // Inclure le fichier contenant la base URL
include '../classes/DBConnection.php';
include '../classes/Utilisateur.php';
include '../controller/LoginController.php';

use App\Controller\LoginController;

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);

    // Appel au contrôleur de connexion
    $controller = new LoginController();
    $message = $controller->connexionUtilisateur($email, $mot_de_passe);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Inclusion de jQuery -->
</head>
<body>

    <!-- Inclure la barre de navigation -->
    <?php include('includes/navbar.php'); ?>

    <div class="container">
        <h2>Connexion</h2>

        <div id="message"></div> <!-- Div pour afficher les erreurs/messages -->

        <form id="login-form" action="login.php" method="POST">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>

            <button type="submit">Se connecter</button>
        </form>
    </div>



    <script src="assets/js/login.js"></script>
</body>
</html>
