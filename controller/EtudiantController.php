<?php
namespace App\Controller;

use App\Etudiant;

class EtudiantController {

    public function ajouterEtudiant($data) {
        $nom = $data['nom'];
        $prenom = $data['prenom'];
        $email = $data['email'];
        $classe_id = $data['classe_id'];
        $matricule = $data['matricule'];
        $email_parent = $data['email_parent'];

        // Déterminer le préfixe de la classe (2 premières lettres + dernière lettre)
        $classe = $this->getClassePrefix($classe_id);
        $classePrefix = strtoupper(substr($classe, 0, 2) . substr($classe, -1));

        // Générer le matricule
        $generatedMatricule = Etudiant::generateMatricule($nom, $prenom, $classePrefix);

        // Créer une instance de la classe Etudiant
        $etudiant = new Etudiant($nom, $prenom, $email, $classe_id, $generatedMatricule, $email_parent);

        try {
            // Ajouter l'utilisateur et l'étudiant dans la base de données
            $etudiant->ajouter();
            return ['status' => 'success', 'message' => 'Succès: L\'étudiant a été ajouté avec succès!'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // Récupérer le nom de la classe par son ID
    private function getClassePrefix($classe_id) {
        $pdo = \App\DBConnection::getConnection();
        $stmt = $pdo->prepare("SELECT nom FROM classes WHERE id = ?");
        $stmt->execute([$classe_id]);
        $classe = $stmt->fetch();
        return $classe ? $classe['nom'] : '';
    }


    public function modifierEtudiant($data) {
        $nom = $data['nom'];
        $prenom = $data['prenom'];
        $email = $data['email'];
        $classe_id = $data['classe_id'];
        $matricule = $data['matricule'];
        $email_parent = $data['email_parent'];
    
        // Créer une instance de la classe Etudiant
        $etudiant = new Etudiant($nom, $prenom, $email, $classe_id, $matricule, $email_parent);
    
        try {
            // Modifier l'utilisateur et l'étudiant dans la base de données
            $etudiant->modifierEtudiant();
            return ['status' => 'success', 'message' => 'Succès: L\'étudiant a été modifié avec succès!'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    
    public function supprimerEtudiant($data) {
        $matricule = $data['matricule'];
        $email = $data['email'];
    
        // Créer une instance de la classe Etudiant
        $etudiant = new Etudiant('', '', $email, 0, $matricule, '');
    
        try {
            // Supprimer l'étudiant et l'utilisateur de la base de données
            $etudiant->supprimerEtudiant();
            return ['status' => 'success', 'message' => 'Succès: L\'étudiant a été supprimé avec succès!'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
}
?>
