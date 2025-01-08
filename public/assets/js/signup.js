$(document).ready(function () {
    $('#signup-form').on('submit', function (e) {
        e.preventDefault(); // Empêcher la soumission normale du formulaire

        // Réinitialiser les messages d'erreur
        $('.error').empty().show();

        // Récupérer les données du formulaire
        var formData = $(this).serialize();

        $.ajax({
            url: 'signup.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'error') {
                    // Afficher les messages d'erreur sous les champs correspondants
                    if (response.message.includes('Nom')) {
                        $('#nom-error').text(response.message);
                    }
                    if (response.message.includes('Prénom')) {
                        $('#prenom-error').text(response.message);
                    }
                    if (response.message.includes('Email')) {
                        $('#email-error').text(response.message);
                    }
                    if (response.message.includes('Mot de passe')) {
                        $('#mot_de_passe-error').text(response.message);
                    }
                    if (response.message.includes('correspondent pas')) {
                        $('#mot_de_passe_confirmation-error').text(response.message);
                    }
                } else if (response.status === 'success') {
                    setTimeout(function () {
                        window.location.href = 'index.php'; // Redirection vers la page d'accueil
                    }, 5000);
                }

                // Masquer les messages après 5 secondes
                setTimeout(function () {
                    $('.error').fadeOut();
                }, 5000);
            },
            error: function () {
                alert('Une erreur est survenue. Veuillez réessayer.');
            }
        });
    });
});
