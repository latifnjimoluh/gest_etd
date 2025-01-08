<?php
include 'url.php'; // Inclure le fichier contenant la base URL

// Vérification si l'utilisateur est connecté
$is_logged_in = isset($_SESSION['user']);

// Récupérer l'URL actuelle pour marquer la page active
$current_page = basename($_SERVER['REQUEST_URI']);
?>
<style>
    /* Style pour la barre de navigation */
    nav {
        background-color: #333;
        padding: 10px;
    }

    nav ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
        text-align: center;
    }

    nav ul li {
        display: inline-block;
        margin: 0 15px;
    }

    nav ul li a {
        color: white;
        text-decoration: none;
        font-size: 18px;
        padding: 10px 20px;
        display: block;
    }

    nav ul li a:hover {
        background-color: #575757;
        border-radius: 4px;
    }

    .active {
        background-color: #4CAF50; /* Couleur pour la page active */
        border-radius: 4px;
    }
</style>

<nav>
    <ul>
        <li><a href="<?php echo BASE_URL; ?>index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Accueil</a></li>
        
        <?php if ($is_logged_in): ?>
            <!-- Si l'utilisateur est connecté, afficher les liens Déconnexion et les infos utilisateur -->
            <li><a href="<?php echo BASE_URL; ?>profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">Mon Profil</a></li>
            <li><a href="<?php echo BASE_URL; ?>logout.php" class="<?php echo ($current_page == 'logout.php') ? 'active' : ''; ?>">Se déconnecter</a></li>
            <li><a href="#">Bienvenue, <?php echo $_SESSION['user']['prenom']; ?></a></li> <!-- Afficher le prénom de l'utilisateur -->
        <?php else: ?>
            <!-- Si l'utilisateur n'est pas connecté, afficher les liens Se connecter et S'inscrire -->
            <li><a href="<?php echo BASE_URL; ?>login.php" class="<?php echo ($current_page == 'login.php') ? 'active' : ''; ?>">Se connecter</a></li>
            <li><a href="<?php echo BASE_URL; ?>signin.php" class="<?php echo ($current_page == 'signin.php') ? 'active' : ''; ?>">S'inscrire</a></li>
        <?php endif; ?>
        
        <li><a href="<?php echo BASE_URL; ?>about.php" class="<?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">À propos</a></li>
        <li><a href="<?php echo BASE_URL; ?>contact.php" class="<?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">Contact</a></li>
    </ul>
</nav>
