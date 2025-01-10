<?php
require_once '../classes/Paiement.php';
require_once '../classes/DBConnection.php';

use App\DBConnection;
use App\Paiement;

$conn = DBConnection::getConnection();
$paiement = new Paiement($conn);

$erreur_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $etudiant_id = $_POST['etudiant_id'];
    $montant = $_POST['montant'];

    try {
        $paiement->ajouterPaiement($etudiant_id, $montant);
        $message = 'Paiement ajouté avec succès.';
    } catch (Exception $e) {
        $erreur_message = $e->getMessage();
    }
}

$etudiants_insolvables = $paiement->getEtudiants('insolvable');
$etudiants_solvables = $paiement->getEtudiants('solvable');
