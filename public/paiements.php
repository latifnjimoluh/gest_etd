<?php
require_once '../classes/Paiement.php';
require_once '../classes/DBConnection.php';

$erreur_message = '';
$success_message = '';

$paiement = new App\Paiement();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $etudiant_id = $_POST['etudiant_id'];
    $montant = $_POST['montant'];

    // Traitement du paiement
    $response = $paiement->ajouterPaiement($etudiant_id, $montant);

    if ($response['success']) {
        $paiement->genererRecu($etudiant_id, $montant);
        $success_message = $response['message'];
    } else {
        $erreur_message = $response['message'];
    }
}

// Récupérer les étudiants solvables et insolvables
$etudiants_insolvables = $paiement->getEtudiantsStatut('insolvable');
$etudiants_solvables = $paiement->getEtudiantsStatut('solvable');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Paiements</title>
    <link rel="stylesheet" href="assets/css/paiement.css">
</head>
<body>

<!-- Inclusion de la barre de navigation -->
<?php include('includes/navbar.php'); ?>

<div class="container">
    <h2>Ajouter un Paiement</h2>

    <!-- Affichage des messages -->
    <?php if (!empty($erreur_message)): ?>
        <div id="erreur-message" class="message error"><?= htmlspecialchars($erreur_message) ?></div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div id="success-message" class="message success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <!-- Formulaire de paiement -->
    <form method="POST">
        <div class="form-group">
            <label for="etudiant_id">Sélectionner un Étudiant</label>
            <select id="etudiant_id" name="etudiant_id" class="form-control" required>
                <option value="">-- Sélectionner un étudiant --</option>
                <?php foreach ($etudiants_insolvables as $etudiant): ?>
                    <option value="<?= $etudiant['id'] ?>"><?= htmlspecialchars($etudiant['nom']) . ' ' . htmlspecialchars($etudiant['prenom']) . ' (' . htmlspecialchars($etudiant['matricule']) . ')' ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="montant">Montant</label>
            <input type="number" id="montant" name="montant" class="form-control" required>
        </div>

        <button type="submit" name="action" value="add" class="btn btn-success">Ajouter le Paiement</button>
        <button type="submit" name="action" value="subtract" class="btn btn-danger">Soustraire du Paiement</button>
    </form>

    <h2>Liste des Étudiants</h2>

    <!-- Tableau des étudiants insolvables -->
    <h3>Étudiants Insolvables</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Nom et Prénom / Montant Payé / Montant Restant</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($etudiants_insolvables as $etudiant): 
                $montant_paye = $etudiant['total_paye'] ?: 0;
                $montant_rest = $etudiant['pension'] - $montant_paye;
            ?>
                <tr>
                    <td><?= htmlspecialchars($etudiant['matricule']) ?></td>
                    <td>
                        <?= htmlspecialchars($etudiant['nom']) . ' ' . htmlspecialchars($etudiant['prenom']) ?><br>
                        Montant Payé: <?= number_format($montant_paye, 2) ?> FCFA<br>
                        Montant Restant: <?= number_format($montant_rest, 2) ?> FCFA
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Tableau des étudiants solvables -->
    <h3>Étudiants Solvables</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Nom et Prénom / Montant Payé / Montant Restant</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($etudiants_solvables as $etudiant): 
                $montant_paye = $etudiant['total_paye'] ?: 0;
                $montant_rest = $etudiant['pension'] - $montant_paye;
            ?>
                <tr>
                    <td><?= htmlspecialchars($etudiant['matricule']) ?></td>
                    <td>
                        <?= htmlspecialchars($etudiant['nom']) . ' ' . htmlspecialchars($etudiant['prenom']) ?><br>
                        Montant Payé: <?= number_format($montant_paye, 2) ?> FCFA<br>
                        Montant Restant: <?= number_format($montant_rest, 2) ?> FCFA
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
