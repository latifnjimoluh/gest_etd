<?php
require_once '../classes/DBConnection.php';
require_once '../classes/Etudiant.php';
require_once '../controller/EtudiantController.php';

use App\Controller\EtudiantController;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = $_POST;
    $controller = new EtudiantController();
    $response = $controller->ajouterEtudiant($data);
    echo json_encode($response);
}
?>
