
<?php
// Assurez-vous que session_start() est appelé avant d'utiliser $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si la constante BASE_URL est déjà définie
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/gest_etd/public/');
}
?>
