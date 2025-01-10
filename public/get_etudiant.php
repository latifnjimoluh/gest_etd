<?php
require_once '../classes/DBConnection.php';
require_once '../classes/Etudiant.php';

use App\Etudiant;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $pdo = App\DBConnection::getConnection();
    
    $stmt = $pdo->prepare("SELECT etudiants.matricule, etudiants.email_parent, utilisateurs.nom, utilisateurs.prenom, utilisateurs.email, etudiants.classe_id 
                           FROM etudiants
                           JOIN utilisateurs ON utilisateurs.email = etudiants.email_parent
                           WHERE etudiants.id = ?");
    $stmt->execute([$id]);
    $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($etudiant);
}
?>