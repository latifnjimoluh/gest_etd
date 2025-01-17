<?php
namespace App;

use PDO;
use PDOException;
use Exception;
use FPDF;

class Paiement
{
    private $conn;

    public function __construct()
    {
        $this->conn = DBConnection::getConnection();
    }

    public function ajouterPaiement($etudiant_id, $montant)
    {
        $erreur_message = '';
        $date_versement = date('Y-m-d H:i:s'); // Utiliser la date actuelle

        try {
            // Commencer une transaction pour assurer la cohérence des données
            $this->conn->beginTransaction();

            // Récupérer la pension de l'étudiant
            $stmt = $this->conn->prepare("SELECT pension FROM classes 
                                          JOIN etudiants ON etudiants.classe_id = classes.id 
                                          WHERE etudiants.id = :etudiant_id");
            $stmt->bindParam(':etudiant_id', $etudiant_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $pension = $result['pension'];

            // Calculer le total des paiements de l'étudiant
            $stmt = $this->conn->prepare("SELECT SUM(montant) AS total_paiements 
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
            $stmt = $this->conn->prepare("INSERT INTO paiements (etudiant_id, montant, date_versement) 
                                          VALUES (:etudiant_id, :montant, :date_versement)");
            $stmt->bindParam(':etudiant_id', $etudiant_id);
            $stmt->bindParam(':montant', $montant);
            $stmt->bindParam(':date_versement', $date_versement);
            $stmt->execute();

            // Calculer le nouveau total des paiements de l'étudiant
            $total_paiements += $montant;

            // Vérifier le statut financier de l'étudiant
            $statut_financier = ($total_paiements >= $pension) ? 'solvable' : 'insolvable';
            $stmt = $this->conn->prepare("UPDATE etudiants SET statut_financier = :statut_financier 
                                          WHERE id = :etudiant_id");
            $stmt->bindParam(':statut_financier', $statut_financier);
            $stmt->bindParam(':etudiant_id', $etudiant_id);
            $stmt->execute();

            // Commit de la transaction
            $this->conn->commit();

            return ['success' => true, 'message' => 'Paiement ajouté avec succès.'];

        } catch (PDOException $e) {
            // En cas d'erreur, annuler la transaction
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Erreur lors de l\'ajout du paiement : ' . $e->getMessage()];
        }
    }

    public function genererRecu($etudiant_id, $montant)
    {
        // Récupérer les informations de l'étudiant pour le reçu
        $stmt = $this->conn->prepare("SELECT utilisateurs.nom, utilisateurs.prenom, utilisateurs.email, etudiants.matricule, classes.nom AS classe
                                      FROM etudiants
                                      JOIN utilisateurs ON etudiants.email_parent = utilisateurs.email
                                      JOIN classes ON etudiants.classe_id = classes.id
                                      WHERE etudiants.id = :etudiant_id");
        $stmt->bindParam(':etudiant_id', $etudiant_id);
        $stmt->execute();
        $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

        // Générer le reçu PDF
        require_once '../vendor/autoload.php';
        require('../vendor/fpdf/fpdf/src/Fpdf/Fpdf.php');
        $pdf = new FPDF();
        $pdf->AddPage();

        // Titre du reçu
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell(200, 10, "Reçu de Paiement", 0, 1, 'C');
        $pdf->Ln(10); // Ligne vide pour espacement

        // Informations sur l'étudiant
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, "Nom de l'étudiant: ", 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $etudiant['nom'] . " " . $etudiant['prenom'], 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, "Matricule: ", 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $etudiant['matricule'], 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, "Classe: ", 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $etudiant['classe'], 0, 1);

        // Montant et détails du paiement
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, "Montant payé: ", 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, number_format($montant, 2) . " FCFA", 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 10, "Date de paiement: ", 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, date('Y-m-d H:i:s'), 0, 1);

        // Ligne de séparation
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell(0, 10, "Merci pour votre paiement!", 0, 1, 'C');

        // Générer le fichier PDF
        $pdf->Output('D', 'recu_paiement_' . $etudiant['matricule'] . '.pdf'); // Télécharger le fichier PDF
    }

    // Méthode pour récupérer les étudiants avec leurs paiements
  // Fonction pour récupérer les étudiants solvables et insolvables
  public function getEtudiantsStatut($statut, $search = '')
  {
      try {
          // Ajouter une condition de recherche si un terme de recherche est fourni
          $search_condition = '';
          if (!empty($search)) {
              $search_condition = " AND (utilisateurs.nom LIKE :search OR utilisateurs.prenom LIKE :search)";
          }
  
          // Définir la requête en fonction du statut et de la recherche
          $query = "SELECT etudiants.id, etudiants.matricule, utilisateurs.nom, utilisateurs.prenom, 
                           SUM(paiements.montant) AS total_paye, classes.pension
                    FROM etudiants 
                    JOIN utilisateurs ON etudiants.email_parent = utilisateurs.email
                    JOIN classes ON etudiants.classe_id = classes.id
                    LEFT JOIN paiements ON paiements.etudiant_id = etudiants.id
                    WHERE etudiants.statut_financier = :statut
                    $search_condition
                    GROUP BY etudiants.id";
  
          // Préparer la requête
          $stmt = $this->conn->prepare($query);
          $stmt->bindParam(':statut', $statut);
  
          // Si une recherche est effectuée, lier le paramètre de recherche
          if (!empty($search)) {
              $search_term = '%' . $search . '%';
              $stmt->bindParam(':search', $search_term);
          }
  
          $stmt->execute();
          return $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
          return ['success' => false, 'message' => 'Erreur de récupération des étudiants : ' . $e->getMessage()];
      }
  }
}
?>
