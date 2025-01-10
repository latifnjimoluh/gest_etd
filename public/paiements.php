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
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Récupérer les étudiants solvables et insolvables avec la recherche
$etudiants_insolvables = $paiement->getEtudiantsStatut('insolvable', $search);
$etudiants_solvables = $paiement->getEtudiantsStatut('solvable', $search);

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
    <form method="POST" id="payment-form">
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

        <button type="button" id="add-payment" class="btn btn-success">Ajouter le Paiement</button>
        <button type="button" id="subtract-payment" class="btn btn-danger">Soustraire du Paiement</button>
    </form>

    <h2>Liste des Étudiants</h2>
    <form method="GET" action="">
        <div class="form-group">
            <label for="search">Rechercher un étudiant (Nom ou Prénom)</label>
            <input type="text" id="search" name="search" class="form-control" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        </div>
        <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>

    <!-- Tableau des étudiants insolvables -->
    <h3>Étudiants Insolvables</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Nom</th>
                <th>Montant Payé</th>
                <th>Montant Restant</th>
                <th>Effectuer un Paiement</th>
            </tr>
        </thead>
        <tbody id="insolvables-body">
            <?php foreach ($etudiants_insolvables as $etudiant): 
                $montant_paye = $etudiant['total_paye'] ?: 0;
                $montant_rest = $etudiant['pension'] - $montant_paye;
            ?>
                <tr>
                    <td><?= htmlspecialchars($etudiant['matricule']) ?></td>
                    <td><?= htmlspecialchars($etudiant['nom']) . ' ' . htmlspecialchars($etudiant['prenom']) ?></td>
                    <td><?= number_format($montant_paye, 2) ?> FCFA</td>
                    <td><?= number_format($montant_rest, 2) ?> FCFA</td>
                    <td>
                        <form method="POST" action="">
                            <input type="hidden" name="etudiant_id" value="<?= $etudiant['id'] ?>">
                            <input type="number" name="montant" required class="form-control" placeholder="Montant">
                            <button type="submit" class="btn btn-success">Effectuer un Paiement</button>
                        </form>
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
                <th>Nom</th>
                <th>Montant Payé</th>
                <th>Montant Restant</th>
            </tr>
        </thead>
        <tbody id="solvables-body">
            <?php foreach ($etudiants_solvables as $etudiant): 
                $montant_paye = $etudiant['total_paye'] ?: 0;
                $montant_rest = $etudiant['pension'] - $montant_paye;
            ?>
                <tr>
                    <td><?= htmlspecialchars($etudiant['matricule']) ?></td>
                    <td><?= htmlspecialchars($etudiant['nom']) . ' ' . htmlspecialchars($etudiant['prenom']) ?></td>
                    <td><?= number_format($montant_paye, 2) ?> FCFA</td>
                    <td><?= number_format($montant_rest, 2) ?> FCFA</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    document.getElementById('subtract-payment').addEventListener('click', function() {
        const montantInput = document.getElementById('montant');
        // Ajouter un signe négatif au montant avant l'envoi
        montantInput.value = -Math.abs(montantInput.value);
        document.getElementById('payment-form').submit();  // Soumettre le formulaire
    });

    document.getElementById('add-payment').addEventListener('click', function() {
        const montantInput = document.getElementById('montant');
        // Envoyer le formulaire normalement
        document.getElementById('payment-form').submit();
    });
</script>

</body>
</html>
