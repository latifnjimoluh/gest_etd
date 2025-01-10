<?php
require_once '../classes/DBConnection.php';
require_once '../vendor/autoload.php'; // Inclure FPDF si vous utilisez Composer

$conn = App\DBConnection::getConnection();

$erreur_message = ''; // Variable pour stocker les erreurs

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données envoyées par le formulaire
    $etudiant_id = $_POST['etudiant_id'];
    $montant = $_POST['montant'];
    $date_versement = date('Y-m-d H:i:s'); // Utiliser la date actuelle

    try {
        // Commencer une transaction pour assurer la cohérence des données
        $conn->beginTransaction();

        // Récupérer la pension de l'étudiant
        $stmt = $conn->prepare("SELECT pension FROM classes 
                                JOIN etudiants ON etudiants.classe_id = classes.id 
                                WHERE etudiants.id = :etudiant_id");
        $stmt->bindParam(':etudiant_id', $etudiant_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $pension = $result['pension'];

        // Calculer le total des paiements de l'étudiant
        $stmt = $conn->prepare("SELECT SUM(montant) AS total_paiements 
                                FROM paiements 
                                WHERE etudiant_id = :etudiant_id");
        $stmt->bindParam(':etudiant_id', $etudiant_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_paiements = $result['total_paiements'] ?: 0;

        // Vérifier si le montant ne dépasse pas le montant restant
        if ($montant < 0 && abs($montant) > $total_paiements) {
            $erreur_message = "Erreur : Le montant à soustraire est plus élevé que le montant déjà payé.";
            throw new Exception($erreur_message);
        }

        // Insérer les données dans la table paiements
        $stmt = $conn->prepare("INSERT INTO paiements (etudiant_id, montant, date_versement) 
                                VALUES (:etudiant_id, :montant, :date_versement)");
        $stmt->bindParam(':etudiant_id', $etudiant_id);
        $stmt->bindParam(':montant', $montant);
        $stmt->bindParam(':date_versement', $date_versement);
        $stmt->execute();

        // Calculer le nouveau total des paiements de l'étudiant
        $total_paiements += $montant;

        // Vérifier le statut financier de l'étudiant
        $statut_financier = ($total_paiements >= $pension) ? 'solvable' : 'insolvable';
        $stmt = $conn->prepare("UPDATE etudiants SET statut_financier = :statut_financier 
                                WHERE id = :etudiant_id");
        $stmt->bindParam(':statut_financier', $statut_financier);
        $stmt->bindParam(':etudiant_id', $etudiant_id);
        $stmt->execute();

        // Commit de la transaction
        $conn->commit();

        // Récupérer les informations de l'étudiant pour le reçu
        $stmt = $conn->prepare("SELECT utilisateurs.nom, utilisateurs.prenom, utilisateurs.email, etudiants.matricule
                                FROM etudiants
                                JOIN utilisateurs ON etudiants.email_parent = utilisateurs.email
                                WHERE etudiants.id = :etudiant_id");
        $stmt->bindParam(':etudiant_id', $etudiant_id);
        $stmt->execute();
        $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

        // Générer le reçu PDF
        require('../vendor/fpdf/fpdf/src/Fpdf/Fpdf.php');
        $pdf = new FPDF();
        $pdf->AddPage();

        // Titre
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(200, 10, "Reçu de Paiement", 0, 1, 'C');

        // Informations sur l'étudiant
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(50, 10, "Nom: " . $etudiant['nom'] . " " . $etudiant['prenom'], 0, 1);
        $pdf->Cell(50, 10, "Matricule: " . $etudiant['matricule'], 0, 1);
        $pdf->Cell(50, 10, "Email: " . $etudiant['email'], 0, 1);

        // Montant et détails du paiement
        $pdf->Cell(50, 10, "Montant payé: " . number_format($montant, 2) . " €", 0, 1);
        $pdf->Cell(50, 10, "Date de paiement: " . $date_versement, 0, 1);
        $pdf->Cell(50, 10, "Montant restant: " . number_format($pension - $total_paiements, 2) . " €", 0, 1);

        // Ajouter une ligne de séparation
        $pdf->Ln(10);
        $pdf->Cell(200, 10, "Merci pour votre paiement!", 0, 1, 'C');

        // Générer le fichier PDF
        $pdf->Output('D', 'recu_paiement_' . $etudiant['matricule'] . '.pdf'); // Télécharger le fichier PDF

        echo 'Paiement ajouté avec succès et reçu généré.';
    } catch (PDOException $e) {
        // En cas d'erreur, annuler la transaction
        $conn->rollBack();
        echo 'Erreur lors de l\'ajout du paiement : ' . $e->getMessage();
    }
}


// Recherche des étudiants avec AJAX
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Etudiants insolvables
$query = "SELECT etudiants.id, etudiants.matricule, utilisateurs.nom, utilisateurs.prenom 
          FROM etudiants 
          JOIN utilisateurs ON etudiants.email_parent = utilisateurs.email
          WHERE etudiants.statut_financier = 'insolvable'";

if ($search_term) {
    $query .= " AND (utilisateurs.nom LIKE :search_term OR utilisateurs.prenom LIKE :search_term OR etudiants.matricule LIKE :search_term)";
}

$stmt = $conn->prepare($query);
if ($search_term) {
    $search_term = '%' . $search_term . '%';
    $stmt->bindParam(':search_term', $search_term);
}

$stmt->execute();
$etudiants_insolvables = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Etudiants solvables
$query_sol = "SELECT etudiants.id, etudiants.matricule, utilisateurs.nom, utilisateurs.prenom 
              FROM etudiants 
              JOIN utilisateurs ON etudiants.email_parent = utilisateurs.email
              WHERE etudiants.statut_financier = 'solvable'";

$stmt = $conn->prepare($query_sol);
$stmt->execute();
$etudiants_solvables = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Paiements</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<h2>Ajouter un Paiement</h2>

<!-- Affichage des erreurs -->
<div id="erreur-message" style="color: red;"><?= htmlspecialchars($erreur_message) ?></div>

<form id="form-ajout-paiement" method="POST">
    <label for="etudiant_id">Sélectionner un Étudiant</label>
    <select id="etudiant_id" name="etudiant_id" required>
        <option value="">-- Sélectionner un étudiant --</option>
        <?php foreach ($etudiants_insolvables as $etudiant): ?>
            <option value="<?= $etudiant['id'] ?>"><?= $etudiant['nom'] . ' ' . $etudiant['prenom'] . ' (' . $etudiant['matricule'] . ')' ?></option>
        <?php endforeach; ?>
    </select>

    <label for="montant">Montant</label>
    <input type="number" id="montant" name="montant" required>

    <button type="submit" name="action" value="add">Ajouter le Paiement</button>
    <button type="submit" name="action" value="subtract">Soustraire du Paiement</button>
</form>

<hr>

<h2>Liste des Étudiants Insolvables</h2>

<table border="1" id="students-table">
    <thead>
        <tr>
            <th>Nom et Matricule</th>
            <th>Montant Payé</th>
            <th>Montant Restant</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($etudiants_insolvables as $etudiant): ?>
            <?php 
                $stmt = $conn->prepare("SELECT SUM(montant) AS total_paiements FROM paiements WHERE etudiant_id = :etudiant_id");
                $stmt->bindParam(':etudiant_id', $etudiant['id']);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $total_paiements = $result['total_paiements'] ?: 0;

                $stmt = $conn->prepare("SELECT pension FROM classes JOIN etudiants ON etudiants.classe_id = classes.id WHERE etudiants.id = :etudiant_id");
                $stmt->bindParam(':etudiant_id', $etudiant['id']);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $pension = $result['pension'];
                $montant_restant = $pension - $total_paiements;
            ?>
            <tr>
                <td><?= $etudiant['nom'] . ' ' . $etudiant['prenom'] . ' (' . $etudiant['matricule'] . ')' ?></td>
                <td><?= number_format($total_paiements, 2) ?> €</td>
                <td><?= number_format($montant_restant, 2) ?> €</td>
                <td>
                    <form action="paiement.php" method="POST" style="display:inline;">
                        <input type="hidden" name="etudiant_id" value="<?= $etudiant['id'] ?>">
                        <button type="submit">Ajouter un Paiement</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>Liste des Étudiants Solvables</h2>

<table border="1" id="solvable-students-table">
    <thead>
        <tr>
            <th>Nom et Matricule</th>
            <th>Montant Payé</th>
            <th>Montant Restant</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($etudiants_solvables as $etudiant): ?>
            <?php 
                $stmt = $conn->prepare("SELECT SUM(montant) AS total_paiements FROM paiements WHERE etudiant_id = :etudiant_id");
                $stmt->bindParam(':etudiant_id', $etudiant['id']);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $total_paiements = $result['total_paiements'] ?: 0;

                $stmt = $conn->prepare("SELECT pension FROM classes JOIN etudiants ON etudiants.classe_id = classes.id WHERE etudiants.id = :etudiant_id");
                $stmt->bindParam(':etudiant_id', $etudiant['id']);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $pension = $result['pension'];
                $montant_restant = $pension - $total_paiements;
            ?>
            <tr>
                <td><?= $etudiant['nom'] . ' ' . $etudiant['prenom'] . ' (' . $etudiant['matricule'] . ')' ?></td>
                <td><?= number_format($total_paiements, 2) ?> €</td>
                <td><?= number_format($montant_restant, 2) ?> €</td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
    // AJAX pour la recherche des étudiants
    $('#search').on('input', function() {
        var search_term = $(this).val();

        $.ajax({
            url: 'paiement.php',
            method: 'GET',
            data: { search: search_term },
            success: function(response) {
                // Mettre à jour le contenu du tableau avec la réponse
                $('#students-table tbody').html($(response).find('#students-table tbody').html());
            }
        });
    });
</script>

</body>
</html>
