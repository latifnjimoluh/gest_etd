<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Étudiants</title>
    <link rel="stylesheet" href="assets/css/etudiant.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<?php include('includes/navbar.php'); ?>
<h2>Ajouter / Modifier un Étudiant</h2>

<form id="form-ajout-etudiant">
    <label for="nom">Nom</label>
    <input type="text" id="nom" name="nom" required>

    <label for="prenom">Prénom</label>
    <input type="text" id="prenom" name="prenom" required>
    
    <label for="email">Email</label>
    <input type="email" id="email" name="email" required>

    <label for="classe_id">Classe</label>
    <select id="classe_id" name="classe_id" required>
        <option value="">-- Sélectionnez une classe --</option>
        <?php
        require_once '../classes/DBConnection.php';
        $conn = App\DBConnection::getConnection();
        $stmt = $conn->query("SELECT id, nom, pension FROM classes");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<option value='{$row['id']}' data-pension='{$row['pension']}'>{$row['nom']}</option>";
        }
        ?>
    </select>

    <label for="pension">Montant de la Pension</label>
    <input type="text" id="pension" name="pension" readonly>

    <label for="matricule">Matricule</label>
    <input type="text" id="matricule" name="matricule" readonly>

    <label for="email_parent">Email du Parent</label>
    <input type="email" id="email_parent" name="email_parent" required>

    <button type="submit">Ajouter l'Étudiant</button>
</form>

<div id="message"></div>

<!-- Tableau des étudiants -->
<h2>Liste des Étudiants</h2>
<table id="table-etudiants" border="1">
    <thead>
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Classe</th>
            <th>Matricule</th>
            <th>Email du Parent</th>
            <th>Statut Financier</th> <!-- Nouvelle colonne pour le statut financier -->
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Récupérer les informations des étudiants et de leur classe
        $stmt = $conn->query("SELECT etudiants.matricule, etudiants.email_parent, utilisateurs.nom, utilisateurs.prenom, utilisateurs.email, classes.nom AS classe_nom, etudiants.classe_id, etudiants.id AS etudiant_id 
                              FROM etudiants
                              JOIN utilisateurs ON utilisateurs.email = etudiants.email_parent
                              JOIN classes ON classes.id = etudiants.classe_id");

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Récupérer la pension de la classe
            $classe_id = $row['classe_id'];
            $stmt_pension = $conn->prepare("SELECT pension FROM classes WHERE id = :classe_id");
            $stmt_pension->bindParam(':classe_id', $classe_id);
            $stmt_pension->execute();
            $classe = $stmt_pension->fetch(PDO::FETCH_ASSOC);
            $pension = $classe['pension'];

            // Récupérer le total des paiements de l'étudiant
            $stmt_paiements = $conn->prepare("SELECT SUM(montant) AS total_paiements FROM paiements WHERE etudiant_id = :etudiant_id");
            $stmt_paiements->bindParam(':etudiant_id', $row['etudiant_id']);
            $stmt_paiements->execute();
            $paiements = $stmt_paiements->fetch(PDO::FETCH_ASSOC);
            $total_paiements = $paiements['total_paiements'];

            // Déterminer le statut financier
            $statut_financier = ($total_paiements >= $pension) ? 'Solvable' : 'Insolvable';

            // Afficher les données de l'étudiant
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['nom']) . "</td>";
            echo "<td>" . htmlspecialchars($row['prenom']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['classe_nom']) . "</td>";
            echo "<td>" . htmlspecialchars($row['matricule']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email_parent']) . "</td>";
            echo "<td>" . $statut_financier . "</td>";  // Affichage du statut financier
            // Colonne pour les actions
            echo "<td>
                    <button class='edit-btn' data-id='" . $row['etudiant_id'] . "'>Modifier</button>
                    <button class='delete-btn' data-id='" . $row['etudiant_id'] . "'>Supprimer</button>
                  </td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>


<script>
$(document).ready(function () {
    // Fonction pour générer le matricule
    function generateMatricule() {
        var nom = $('#nom').val().substring(0, 2).toUpperCase(); // 2 premiers caractères du nom
        var prenom = $('#prenom').val().substring(0, 2).toUpperCase(); // 2 premiers caractères du prénom
        var classe = $('#classe_id').find(':selected').text();
        var classePrefix = classe ? classe.substring(0, 2).toUpperCase() + classe.slice(-1).toUpperCase() : ''; // 2 premiers caractères et le dernier de la classe

        // Génération aléatoire de 3 chiffres
        var randomDigits = Math.floor(100 + Math.random() * 900);

        // Matricule final
        var matricule = `ETD${classePrefix}${nom}${prenom}${randomDigits}`;
        $('#matricule').val(matricule);
    }

    // Mettre à jour le montant de la pension lorsque la classe est sélectionnée
    $('#classe_id').on('change', function () {
        var pension = $(this).find(':selected').data('pension');
        $('#pension').val(pension || '');
        generateMatricule(); // Met à jour le matricule en fonction de la classe
    });

    // Génération automatique du matricule lors de la saisie du prénom et du nom
    $('#nom, #prenom').on('input', function () {
        generateMatricule();
    });

    // Gestion de la soumission du formulaire
    $('#form-ajout-etudiant').on('submit', function (e) {
        e.preventDefault();

        var formData = $(this).serialize();

        $.ajax({
            url: 'ajout_etudiant.php',
            method: 'POST',
            data: formData,
            success: function (response) {
                $('#message').html(response);
                if (response.includes('Étudiant ajouté avec succès')) {
                    $('#form-ajout-etudiant')[0].reset();
                    $('#pension').val('');
                    $('#matricule').val('');
                    location.reload(); // Recharge la page pour afficher les nouveaux étudiants
                }
            },
            error: function () {
                $('#message').html('<p class="error">Erreur lors de l\'ajout de l\'étudiant.</p>');
            }
        });
    });

    // Modifier un étudiant
    $(document).on('click', '.edit-btn', function () {
        var id = $(this).data('id');
        $.ajax({
            url: 'get_etudiant.php',
            method: 'GET',
            data: { id: id },
            success: function (data) {
                var etudiant = JSON.parse(data);
                $('#nom').val(etudiant.nom);
                $('#prenom').val(etudiant.prenom);
                $('#email').val(etudiant.email);
                $('#classe_id').val(etudiant.classe_id);
                $('#matricule').val(etudiant.matricule);
                $('#email_parent').val(etudiant.email_parent);
                $('#form-ajout-etudiant').attr('action', 'modifier_etudiant.php'); // Modifier l'action du formulaire
                $('button[type="submit"]').text('Modifier l\'Étudiant');
            }
        });
    });

    // Supprimer un étudiant
    $(document).on('click', '.delete-btn', function () {
        var matricule = $(this).data('id');
        if (confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ?')) {
            $.ajax({
                url: 'supprimer_etudiant.php',
                method: 'POST',
                data: { matricule: matricule },
                success: function (response) {
                    if (response.status === 'success') {
                        location.reload(); // Recharge la page pour supprimer l'étudiant
                    } else {
                        alert('Erreur: ' + response.message);
                    }
                }
            });
        }
    });
});
</script>

</body>
</html>
