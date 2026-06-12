<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Etudiant;
use App\Models\Professeur;
use App\Models\Planning;
use App\Models\Salle;
use Carbon\Carbon;

class ExcelParserService
{
    public function importUnifiedFile($filePath)
    {
        \Log::info("Début importation unifiée: " . $filePath);
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheetNames = $spreadsheet->getSheetNames();
            \Log::info("Feuilles trouvées: " . json_encode($sheetNames));
            
            // Chercher les feuilles (flexible avec la casse)
            $sallesSheet = $this->findSheet($spreadsheet, ['Salles', 'salles', 'SALLES']);
            $profsSheet = $this->findSheet($spreadsheet, ['Professeurs', 'professeurs', 'PROFESSEURS', 'Profs', 'profs']);
            $etudiantsSheet = $this->findSheet($spreadsheet, ['Étudiants', 'Etudiants', 'etudiants', 'ETUDIANTS', 'Etudiant', 'etudiant']);
            
            if ($sallesSheet) {
                \Log::info("✓ Traitement feuille: Salles");
                $this->saveSallesToDatabase($sallesSheet);
            } else {
                \Log::warning("✗ Feuille Salles non trouvée");
            }
            
            if ($profsSheet) {
                \Log::info("✓ Traitement feuille: Professeurs");
                $this->saveToDatabase($profsSheet, Professeur::class, [
                    'nom'        => 'A', 
                    'prenom'     => 'B', 
                    'specialite' => 'C'
                ]);
            } else {
                \Log::warning("✗ Feuille Professeurs non trouvée");
            }
            
            // Importer tous les étudiants (TDIA + ID ensemble)
            if ($etudiantsSheet) {
                \Log::info("✓ Traitement feuille: Étudiants (TDIA + ID)");
                $this->saveToDatabase($etudiantsSheet, Etudiant::class, [
                    'nom'     => 'A',
                    'prenom'  => 'B',
                    'filiere' => 'C',
                    'langue'  => 'D'
                ]);
            } else {
                \Log::warning("✗ Feuille Étudiants non trouvée");
            }
            
            \Log::info("✓ Importation unifiée terminée");
        } catch (\Exception $e) {
            \Log::error("✗ Erreur importation unifiée: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cherche une feuille avec des variantes de noms (insensible à la casse)
     */
    private function findSheet($spreadsheet, $possibleNames)
    {
        $sheetNames = $spreadsheet->getSheetNames();
        \Log::debug("Cherche feuille parmi: " . json_encode($sheetNames));
        
        foreach ($possibleNames as $name) {
            foreach ($sheetNames as $sheetName) {
                if (strtolower(trim($sheetName)) === strtolower(trim($name))) {
                    \Log::info("✓ Feuille trouvée: '$sheetName'");
                    return $spreadsheet->getSheetByName($sheetName);
                }
            }
        }
        \Log::debug("✗ Aucune feuille trouvée pour: " . json_encode($possibleNames));
        return null;
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
        $skipped = 0;
        $errors = [];
        \Log::info("saveToDatabase: modèle=$model, lignes totales=$highestRow");
        $startRow = 2;
        
        for ($row = $startRow; $row <= $highestRow; $row++) {
            $data = [];
            
            $data = array_merge($defaults, $data);
            
            foreach ($mapping as $field => $column) {
                $cellValue = $sheet->getCell($column . $row)->getValue();
                // Nettoyer les espaces supplémentaires et accents
                $data[$field] = $cellValue !== null ? trim($cellValue) : '';
            }
            
            \Log::debug("Ligne $row brute: " . json_encode($data));
            
            // Vérifier que au moins nom OU prenom n'est pas vide
            if (empty($data['nom']) && empty($data['prenom'])) {
                $skipped++;
                \Log::debug("Ligne $row ignorée: nom et prenom vides");
                continue;
            }
            
            // Si nom ou prenom est vide, utiliser une valeur par défaut
            if (empty($data['nom'])) {
                $data['nom'] = 'N/A';
            }
            if (empty($data['prenom'])) {
                $data['prenom'] = 'N/A';
            }

            // Normalisation de la langue pour la table etudiants (enum 'fr', 'en')
            if (isset($data['langue'])) {
                $lang = strtolower(trim($data['langue']));
                if ($lang === 'an' || str_contains($lang, 'anglais') || str_contains($lang, 'en')) {
                    $data['langue'] = 'en';
                } else {
                    $data['langue'] = 'fr'; // par défaut
                }
            }
            
            try {
                // Chercher si existe déjà (insensible à la casse et accents)
                $existing = $model::whereRaw('LOWER(CONCAT(nom, prenom)) = LOWER(CONCAT(?, ?))', 
                    [$data['nom'], $data['prenom']])->first();
                
                if ($existing) {
                    // Mise à jour
                    $existing->update($data);
                    \Log::info("Ligne $row mise à jour: {$data['nom']} {$data['prenom']}");
                } else {
                    // Création
                    $model::create($data);
                    \Log::info("Ligne $row créée: {$data['nom']} {$data['prenom']}");
                }
                $count++;
            } catch (\Exception $e) {
                $skipped++;
                $errorMsg = "Ligne $row erreur: " . $e->getMessage();
                $errors[] = $errorMsg;
                \Log::error($errorMsg);
            }
        }
        \Log::info("Importation terminée: $count lignes traitées, $skipped ignorées");
        if (!empty($errors)) {
            \Log::warning("Erreurs: " . json_encode($errors));
        }
        return $count;
    }

    private function saveSallesToDatabase($sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $count = 0;
        \Log::info("saveSallesToDatabase: lignes=$highestRow");
        
        for ($row = 2; $row <= $highestRow; $row++) {
            $nom = $sheet->getCell('A' . $row)->getValue();
            $type = $sheet->getCell('B' . $row)->getValue();
            
            $nom = $nom !== null ? trim($nom) : '';
            $type = $type !== null ? trim($type) : 'Salle';
            
            \Log::debug("Ligne $row: Nom=$nom, Type=$type");
            
            // Vérifier que le nom n'est pas vide
            if (!empty($nom)) {
                try {
                    // Valider le type
                    if (!in_array($type, ['Salle', 'Amphi'])) {
                        $type = 'Salle';
                    }
                    
                    Salle::updateOrCreate(
                        ['nom' => $nom], 
                        ['type' => $type]
                    );
                    $count++;
                    \Log::info("Ligne $row créée/mise à jour");
                } catch (\Exception $e) {
                    \Log::error("Erreur lors de l'importation ligne $row: " . $e->getMessage());
                }
            }
        }
        \Log::info("Importation salles terminée: $count lignes insérées/mises à jour");
    }
}