$(document).ready(function() {
    // Gestion du formulaire de connexion avec AJAX
    $('#login-form').on('submit', function(e) {
        e.preventDefault(); // Empêche l'envoi traditionnel du formulaire

        var email = $('#email').val();
        var mot_de_passe = $('#mot_de_passe').val();

        // Envoi de la requête AJAX
        $.ajax({
            url: 'login.php',
            type: 'POST',
            data: {
                email: email,
                mot_de_passe: mot_de_passe
            },
            success: function(response) {
                $('#message').html(response); // Affiche la réponse du serveur
                if (response.includes('Connexion réussie')) {
                    window.location.href = 'index.php'; // Redirection si la connexion est réussie
                }
            },
            error: function() {
                $('#message').html('<p class="error">Une erreur s\'est produite. Veuillez réessayer.</p>');
            }
        });
    });
});