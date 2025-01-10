<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue à l'Établissement Scolaire</title>
    <!-- <link rel="stylesheet" href="public/assets/css/style.css"> -->
    <style>
        /* Style global pour la page */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }


        /* Style pour la section d'introduction */
        .hero-section {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 80px 20px;
        }

        .hero-section h1 {
            font-size: 48px;
            margin: 0;
        }

        .hero-section p {
            font-size: 20px;
            margin: 20px 0;
        }

        .btn {
            background-color: #333;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            font-size: 18px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #575757;
        }

        .container {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }

        .footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            nav ul li {
                display: block;
                margin: 10px 0;
            }

            .hero-section h1 {
                font-size: 36px;
            }

            .hero-section p {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>

    <!-- Inclure la barre de navigation -->
    <?php include('includes/navbar.php'); ?>

    <!-- Section d'introduction (hero section) -->
    <div class="hero-section">
        <h1>Bienvenue à l'Établissement Scolaire</h1>
        <p>Un établissement qui met l'accent sur l'excellence académique et le développement personnel de chaque étudiant.</p>
        <a href="login.php" class="btn">Se connecter</a>
    </div>

    <!-- Section d'informations -->
    <div class="container">
        <h2>Nos Services</h2>
        <p>Découvrez les différents services que nous offrons pour soutenir nos étudiants dans leur parcours académique :</p>
        <ul style="list-style-type: none; padding: 0;">
            <li>🔹 Gestion des inscriptions des étudiants</li>
            <li>🔹 Suivi des paiements et des finances</li>
            <li>🔹 Enregistrement des évaluations et des notes</li>
            <li>🔹 Emploi du temps personnalisé</li>
            <li>🔹 Recommandations basées sur les performances académiques</li>
        </ul>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2025 Établissement Scolaire - Tous droits réservés</p>
    </div>

</body>
</html>
