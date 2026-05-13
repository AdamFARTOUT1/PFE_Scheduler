<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExcelParserService;

class ImportRealData extends Command
{
    protected $signature = 'import:real';
    protected $description = 'Importe les vrais fichiers Excel en utilisant le service interne';

    public function handle()
    {
        $excelService = app(ExcelParserService::class);

        // 1. Import des professeurs
        $profsPath = 'C:/Users/pc/Desktop/pfe_webproject/Liste des Profs.xlsx';
        if (!file_exists($profsPath)) {
            $this->error("Fichier professeurs introuvable : $profsPath");
            return;
        }
        $excelService->importProfesseurs($profsPath);
        $this->info('Professeurs importés avec succès.');

        // 2. Import des étudiants ID
        $idPath = 'C:/Users/pc/Desktop/pfe_webproject/Ingénierie des données 3_Email.xlsx';
        if (!file_exists($idPath)) {
            $this->error("Fichier ID introuvable : $idPath");
            return;
        }
        $excelService->importEtudiantsMultiSheets($idPath, 'ID');
        $this->info('Étudiants ID importés avec succès.');

        // 3. Import des étudiants TDIA
        $tdiaPath = 'C:/Users/pc/Desktop/pfe_webproject/Transformation Digitale & Intelligence Artificielle 3_Email.xlsx';
        if (!file_exists($tdiaPath)) {
            $this->error("Fichier TDIA introuvable : $tdiaPath");
            return;
        }
        $excelService->importEtudiantsMultiSheets($tdiaPath, 'TDIA');
        $this->info('Étudiants TDIA importés avec succès.');

        $this->info('Import complet terminé.');
    }
}