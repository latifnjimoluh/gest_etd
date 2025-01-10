<?php
require_once '../classes/DBConnection.php';
require_once '../classes/Etudiant.php';

use App\Etudiant;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['matricule'])) {
    $matricule = $_POST['matricule'];
    
    // Créer une instance de la classe Etudiant
    $etudiant = new Etudiant('', '', '', 0, $matricule, '');
    try {
        $etudiant->supprimerEtudiant();
        echo json_encode(['status' => 'success', 'message' => 'Étudiant supprimé avec succès']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
