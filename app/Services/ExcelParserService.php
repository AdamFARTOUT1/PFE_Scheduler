<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Etudiant;
use App\Models\Professeur;
use App\Models\Planning;
use Carbon\Carbon;

class ExcelParserService
{
    public function importProfesseurs($filePath)
    {
        \Log::info("Début importation professeurs: " . $filePath);
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            \Log::info("Feuille chargée, lignes: " . $sheet->getHighestRow());
            $this->saveToDatabase($sheet, Professeur::class, [
                'nom'        => 'B', 
                'prenom'     => 'C', 
                'specialite' => 'D'
            ]);
            \Log::info("Importation professeurs terminée");
        } catch (\Exception $e) {
            \Log::error("Erreur importation professeurs: " . $e->getMessage());
            throw $e;
        }
    }    

public function importEtudiantsMultiSheets($filePath, $filiere = 'ID')
{
    \Log::info("Début importation étudiants: " . $filePath . ", filière: $filiere");
    try {
        $spreadsheet = IOFactory::load($filePath);
        $sheetNames = $spreadsheet->getSheetNames();
        \Log::info("Feuilles trouvées: " . json_encode($sheetNames));
        
        foreach ($sheetNames as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            \Log::info("Traitement feuille: $sheetName");
            $this->saveToDatabase($sheet, Etudiant::class, [
                'nom'     => 'B',
                'prenom'  => 'C'
            ], [
                'filiere' => $filiere,
                'langue'  => 'FR'
            ]);
        }
        \Log::info("Importation étudiants terminée");
    } catch (\Exception $e) {
        \Log::error("Erreur importation étudiants: " . $e->getMessage());
        throw $e;
    }
}

    public function checkConformity()
    {
        $anomalies = [];

        $profs = Professeur::withCount('etudiants')->get();
        foreach ($profs as $prof){
            if ($prof->etudiants_count < 3 || $prof->etudiants_count > 4){
                $anomalies[] = "Répartition : Le Prof. {$prof->nom} a {$prof->etudiants_count} étudiants.";
            }
        }

        $plannings = Planning::with('creneau')->get(); 
        foreach ($plannings as $p1){
            foreach ($plannings as $p2){
                if ($p1->id == $p2->id) continue;
                
                $profsP1 = [$p1->encadrant_id, $p1->jury2_id, $p1->jury3_id];
                $profsP2 = [$p2->encadrant_id, $p2->jury2_id, $p2->jury3_id];
                $commonProfs = array_intersect($profsP1, $profsP2);

                if (!empty($commonProfs)){
                    if ($p1->creneau_id == $p2->creneau_id){
                        $anomalies[] = "Conflit : Un professeur est affecté à deux soutenances en même temps (Créneau {$p1->creneau_id}).";
                    }
                    
                    if ($p1->creneau && $p2->creneau && $p1->creneau->date_pfe == $p2->creneau->date_pfe) {
                        $h1_fin = Carbon::parse($p1->creneau->heure_fin);
                        $h2_debut = Carbon::parse($p2->creneau->heure_debut);
                        $diff = $h1_fin->diffInMinutes($h2_debut);
                        if ($diff > 0 && $diff < 60) {
                            $anomalies[] = "Repos : Moins d'une heure de repos pour un professeur entre deux soutenances.";
                        }
                    }
                }
            }
        }
        return $anomalies;
    }

    private function saveToDatabase($sheet, $model, $mapping, $defaults = [])
    {
        $highestRow = $sheet->getHighestRow();
        $count = 0;
        \Log::info("saveToDatabase: modèle=$model, lignes=$highestRow");
        
        for ($row = 2; $row <= $highestRow; $row++) {
            $data = [];
            
            // Ajouter d'abord les valeurs par défaut
            $data = array_merge($defaults, $data);
            
            // Ensuite lire les colonnes du mapping
            foreach ($mapping as $field => $column) {
                $cellValue = $sheet->getCell($column . $row)->getValue();
                $data[$field] = $cellValue !== null ? trim($cellValue) : '';
            }
            
            \Log::debug("Ligne $row: " . json_encode($data));
            
            // Vérifier que nom et prenom ne sont pas vides
            if (!empty($data['nom']) && !empty($data['prenom'])) {
                try {
                    $model::updateOrCreate(
                        ['nom' => $data['nom'], 'prenom' => $data['prenom']], 
                        $data
                    );
                    $count++;
                    \Log::info("Ligne $row créée/mise à jour");
                } catch (\Exception $e) {
                    // Log l'erreur mais continue l'importation
                    \Log::error("Erreur lors de l'importation ligne $row: " . $e->getMessage());
                }
            }
        }
        \Log::info("Importation terminée: $count lignes insérées/mises à jour");
    }
}