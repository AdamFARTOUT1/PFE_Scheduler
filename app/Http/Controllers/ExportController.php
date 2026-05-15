<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use App\Models\Professeur;
use App\Models\Etudiant;
use App\Models\Salle;
use App\Models\Creneau;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Cell;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    public function index()
    {
        return view('export.index');
    }

    /**
     * Generate export based on document type and format.
     */
    public function generate(string $doc, string $format)
    {
        $plannings = Planning::select('plannings.*')
            ->join('creneaux', 'plannings.creneau_id', '=', 'creneaux.id')
            ->with(['etudiant', 'encadrant', 'jury2', 'jury3', 'jury4', 'salle', 'creneau'])
            ->orderBy('creneaux.date_pfe')
            ->orderBy('creneaux.heure_debut')
            ->get();

        if ($plannings->isEmpty()) {
            return redirect()->route('export.index')
                ->with('error', 'Aucun planning à exporter. Veuillez d\'abord générer le planning.');
        }

        if ($doc === 'planning') {
            return $this->exportPlanning($plannings, $format);
        }

        if ($doc === 'affectation') {
            return $this->exportAffectation($plannings, $format);
        }

        if ($doc === 'pvs') {
            return $this->exportPVs($plannings, $format);
        }

        return redirect()->route('export.index')
            ->with('error', 'Type de document inconnu.');
    }

    // =============================================
    //  EXPORT PLANNING
    // =============================================
    private function exportPlanning($plannings, string $format)
    {
        if ($format === 'pdf') {
            return $this->exportPlanningPdf($plannings);
        }
        // Default: XLSX (also for docx fallback)
        return $this->download();
    }

    private function exportPlanningPdf($plannings)
    {
        $jours = $plannings->pluck('creneau.date_pfe')->unique()->filter()->sort();
        $pdf = Pdf::loadView('export.pdf.planning', compact('plannings', 'jours'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Planning_PFE_' . date('Y-m-d') . '.pdf');
    }

    // =============================================
    //  EXPORT AFFECTATION
    // =============================================
    private function exportAffectation($plannings, string $format)
    {
        if ($format === 'pdf') {
            return $this->exportAffectationPdf($plannings);
        }
        return $this->exportAffectationDocx($plannings);
    }

    private function exportAffectationPdf($plannings)
    {
        // Grouper par encadrant
        $parEncadrant = $plannings->groupBy(function ($p) {
            return ($p->encadrant->nom ?? '') . ' ' . ($p->encadrant->prenom ?? '');
        })->sortKeys();

        $pdf = Pdf::loadView('export.pdf.affectation', compact('parEncadrant'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Affectation_PFE_' . date('Y-m-d') . '.pdf');
    }

    private function exportAffectationDocx($plannings)
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection();

        // Titre
        $section->addText(
            'Affectation des Encadrants — PFE 2025/2026',
            ['bold' => true, 'size' => 16, 'color' => '2D6ABF'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 300]
        );
        $section->addText(
            'ENSA Al Hoceima — Département MI',
            ['size' => 11, 'color' => '666666'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 400]
        );

        // Grouper par encadrant
        $parEncadrant = $plannings->groupBy('encadrant_id');

        foreach ($parEncadrant as $encadrantId => $etudiants) {
            $encadrant = $etudiants->first()->encadrant;
            $nomEncadrant = ($encadrant->nom ?? '') . ' ' . ($encadrant->prenom ?? '');

            $section->addText(
                $nomEncadrant . ' (' . $etudiants->count() . ' étudiants)',
                ['bold' => true, 'size' => 13, 'color' => '1A4F9C'],
                ['spaceAfter' => 100, 'spaceBefore' => 200]
            );

            // Tableau
            $table = $section->addTable([
                'borderSize' => 4,
                'borderColor' => 'CCCCCC',
                'cellMargin' => 60,
            ]);

            // En-tête du tableau
            $headerStyle = ['bgColor' => '2D6ABF'];
            $fontHeader = ['bold' => true, 'color' => 'FFFFFF', 'size' => 10];

            $table->addRow();
            $table->addCell(3500, $headerStyle)->addText('Étudiant', $fontHeader);
            $table->addCell(1500, $headerStyle)->addText('Filière', $fontHeader);
            $table->addCell(1500, $headerStyle)->addText('Langue', $fontHeader);
            $table->addCell(2000, $headerStyle)->addText('Date', $fontHeader);
            $table->addCell(1800, $headerStyle)->addText('Horaire', $fontHeader);

            $fontCell = ['size' => 10];
            foreach ($etudiants as $p) {
                $table->addRow();
                $table->addCell(3500)->addText(
                    ($p->etudiant->nom ?? '-') . ' ' . ($p->etudiant->prenom ?? ''),
                    $fontCell
                );
                $table->addCell(1500)->addText($p->etudiant->filiere ?? '-', $fontCell);
                $table->addCell(1500)->addText($p->etudiant->langue ?? '-', $fontCell);
                $table->addCell(2000)->addText($p->creneau->date_pfe ?? '-', $fontCell);
                $table->addCell(1800)->addText(
                    ($p->creneau->heure_debut ?? '-') . ' - ' . ($p->creneau->heure_fin ?? '-'),
                    $fontCell
                );
            }

            $section->addTextBreak();
        }

        $fileName = 'Affectation_PFE_' . date('Y-m-d') . '.docx';
        $tempPath = storage_path('app/' . $fileName);

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    // =============================================
    //  EXPORT PVs
    // =============================================
    private function exportPVs($plannings, string $format)
    {
        $zipFileName = 'PVs_PFE_' . date('Y-m-d') . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return redirect()->route('export.index')
                ->with('error', 'Impossible de créer le fichier ZIP.');
        }

        foreach ($plannings as $index => $p) {
            $phpWord = new PhpWord();
            $phpWord->setDefaultFontName('Arial');
            $phpWord->setDefaultFontSize(11);

            $section = $phpWord->addSection([
                'marginTop' => 800,
                'marginBottom' => 800,
                'marginLeft' => 800,
                'marginRight' => 800,
            ]);

            // En-tête
            $section->addText(
                'ENSA Al Hoceima',
                ['bold' => true, 'size' => 14, 'color' => '2D6ABF'],
                ['alignment' => Jc::CENTER]
            );
            $section->addText(
                'Procès-Verbal de Soutenance PFE — 2025/2026',
                ['bold' => true, 'size' => 12],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 300]
            );

            $section->addText('');

            // Infos étudiant
            $nomEtudiant = ($p->etudiant->nom ?? '-') . ' ' . ($p->etudiant->prenom ?? '');

            $table = $section->addTable(['borderSize' => 4, 'borderColor' => 'CCCCCC', 'cellMargin' => 80]);
            $labelStyle = ['bold' => true, 'size' => 11];
            $valueStyle = ['size' => 11];
            $labelCell = ['bgColor' => 'EBF5FB', 'valign' => 'center'];
            $valueCell = ['valign' => 'center'];

            // Ligne 1
            $table->addRow();
            $table->addCell(2500, $labelCell)->addText('Étudiant', $labelStyle);
            $table->addCell(7500, $valueCell)->addText($nomEtudiant, $valueStyle);

            // Ligne 2
            $table->addRow();
            $table->addCell(2500, $labelCell)->addText('Filière', $labelStyle);
            $table->addCell(7500, $valueCell)->addText($p->etudiant->filiere ?? '-', $valueStyle);

            // Ligne 3
            $table->addRow();
            $table->addCell(2500, $labelCell)->addText('Date', $labelStyle);
            $table->addCell(7500, $valueCell)->addText($p->creneau->date_pfe ?? '-', $valueStyle);

            // Ligne 4
            $table->addRow();
            $table->addCell(2500, $labelCell)->addText('Horaire', $labelStyle);
            $table->addCell(7500, $valueCell)->addText(
                ($p->creneau->heure_debut ?? '-') . ' — ' . ($p->creneau->heure_fin ?? '-'),
                $valueStyle
            );

            // Ligne 5
            $table->addRow();
            $table->addCell(2500, $labelCell)->addText('Salle', $labelStyle);
            $table->addCell(7500, $valueCell)->addText($p->salle->nom ?? '-', $valueStyle);

            $section->addText('');

            // Jury
            $section->addText('Composition du Jury', ['bold' => true, 'size' => 12, 'color' => '1A4F9C'], ['spaceAfter' => 100]);

            $juryTable = $section->addTable(['borderSize' => 4, 'borderColor' => 'CCCCCC', 'cellMargin' => 80]);
            $headerBg = ['bgColor' => '2D6ABF'];
            $headerFont = ['bold' => true, 'color' => 'FFFFFF', 'size' => 10];

            $juryTable->addRow();
            $juryTable->addCell(2500, $headerBg)->addText('Rôle', $headerFont);
            $juryTable->addCell(4000, $headerBg)->addText('Nom & Prénom', $headerFont);
            $juryTable->addCell(3500, $headerBg)->addText('Signature', $headerFont);

            $roles = [
                'Encadrant' => $p->encadrant,
                'Jury 2' => $p->jury2,
                'Jury 3' => $p->jury3,
                'Jury 4' => $p->jury4,
            ];

            foreach ($roles as $role => $prof) {
                if ($prof) {
                    $juryTable->addRow(500);
                    $juryTable->addCell(2500)->addText($role, ['bold' => true, 'size' => 10]);
                    $juryTable->addCell(4000)->addText(
                        ($prof->nom ?? '') . ' ' . ($prof->prenom ?? ''),
                        ['size' => 10]
                    );
                    $juryTable->addCell(3500)->addText('', ['size' => 10]);
                }
            }

            $section->addText('');

            // Grille d'évaluation
            $section->addText('Grille d\'Évaluation', ['bold' => true, 'size' => 12, 'color' => '1A4F9C'], ['spaceAfter' => 100]);

            $evalTable = $section->addTable(['borderSize' => 4, 'borderColor' => 'CCCCCC', 'cellMargin' => 80]);

            $evalTable->addRow();
            $evalTable->addCell(5000, $headerBg)->addText('Critère', $headerFont);
            $evalTable->addCell(2000, $headerBg)->addText('Barème', $headerFont);
            $evalTable->addCell(3000, $headerBg)->addText('Note', $headerFont);

            $criteres = [
                'Rapport écrit' => '/5',
                'Présentation orale' => '/5',
                'Maîtrise technique' => '/5',
                'Réponses aux questions' => '/3',
                'Qualité du travail réalisé' => '/2',
            ];

            foreach ($criteres as $critere => $bareme) {
                $evalTable->addRow(400);
                $evalTable->addCell(5000)->addText($critere, ['size' => 10]);
                $evalTable->addCell(2000, ['alignment' => Jc::CENTER])->addText($bareme, ['size' => 10, 'bold' => true]);
                $evalTable->addCell(3000)->addText('', ['size' => 10]);
            }

            // Total
            $evalTable->addRow(400);
            $evalTable->addCell(5000, ['bgColor' => 'EBF5FB'])->addText('TOTAL', ['bold' => true, 'size' => 11]);
            $evalTable->addCell(2000, ['bgColor' => 'EBF5FB'])->addText('/20', ['bold' => true, 'size' => 11]);
            $evalTable->addCell(3000, ['bgColor' => 'EBF5FB'])->addText('', ['size' => 11]);

            $section->addText('');

            // Observations
            $section->addText('Observations :', ['bold' => true, 'size' => 11], ['spaceAfter' => 50]);
            $section->addText('_______________________________________________________________________________', ['size' => 10]);
            $section->addText('_______________________________________________________________________________', ['size' => 10]);
            $section->addText('_______________________________________________________________________________', ['size' => 10]);

            // Sauvegarder le DOCX temporairement
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nomEtudiant);
            $docxFileName = 'PV_' . $safeName . '.docx';
            $docxPath = storage_path('app/pvs_temp/' . $docxFileName);

            if (!is_dir(storage_path('app/pvs_temp'))) {
                mkdir(storage_path('app/pvs_temp'), 0755, true);
            }

            $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($docxPath);

            $zip->addFile($docxPath, $docxFileName);
        }

        $zip->close();

        // Nettoyer les fichiers temporaires
        $tempFiles = glob(storage_path('app/pvs_temp/*.docx'));
        foreach ($tempFiles as $f) {
            @unlink($f);
        }
        @rmdir(storage_path('app/pvs_temp'));

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    // =============================================
    //  DOWNLOAD EXCEL (legacy)
    // =============================================
    public function download()
    {
        $plannings = Planning::select('plannings.*')
            ->join('creneaux', 'plannings.creneau_id', '=', 'creneaux.id')
            ->with(['etudiant', 'encadrant', 'jury2', 'jury3', 'jury4', 'salle', 'creneau'])
            ->orderBy('creneaux.date_pfe')
            ->orderBy('creneaux.heure_debut')
            ->get();

        if ($plannings->isEmpty()) {
            return redirect()->route('export.index')->with('error', 'Aucun planning à exporter. Veuillez d\'abord générer le planning.');
        }

        $spreadsheet = new Spreadsheet();

        // ── Feuille 1 : Planning complet ──
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Planning Complet');

        // En-têtes
        $headers = ['Jour', 'Heure Début', 'Heure Fin', 'Salle', 'Étudiant', 'Filière', 'Encadrant', 'Jury 2', 'Jury 3', 'Jury 4'];
        foreach ($headers as $col => $header) {
            $cell = chr(65 + $col) . '1';
            $sheet->setCellValue($cell, $header);
        }

        // Style des en-têtes
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2D6ABF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '1A4F9C'],
                ],
            ],
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Données
        $row = 2;
        foreach ($plannings as $p) {
            $sheet->setCellValue("A{$row}", $p->creneau->date_pfe ?? '-');
            $sheet->setCellValue("B{$row}", $p->creneau->heure_debut ?? '-');
            $sheet->setCellValue("C{$row}", $p->creneau->heure_fin ?? '-');
            $sheet->setCellValue("D{$row}", $p->salle->nom ?? '-');
            $sheet->setCellValue("E{$row}", ($p->etudiant->nom ?? '') . ' ' . ($p->etudiant->prenom ?? ''));
            $sheet->setCellValue("F{$row}", $p->etudiant->filiere ?? '-');
            $sheet->setCellValue("G{$row}", ($p->encadrant->nom ?? '') . ' ' . ($p->encadrant->prenom ?? ''));
            $sheet->setCellValue("H{$row}", ($p->jury2->nom ?? '') . ' ' . ($p->jury2->prenom ?? ''));
            $sheet->setCellValue("I{$row}", ($p->jury3->nom ?? '') . ' ' . ($p->jury3->prenom ?? ''));
            $sheet->setCellValue("J{$row}", ($p->jury4->nom ?? '') . ' ' . ($p->jury4->prenom ?? ''));

            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:J{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('EBF5FB');
            }

            $row++;
        }

        // Bordures pour toutes les données
        $dataRange = 'A1:J' . ($row - 1);
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB('CCCCCC');

        // Auto-dimensionner les colonnes
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ── Feuilles par Jour ──
        $jours = $plannings->pluck('creneau.date_pfe')->unique()->filter()->sort();
        foreach ($jours as $jour) {
            $jourSheet = $spreadsheet->createSheet();
            $jourSheet->setTitle(substr($jour, 0, 31));

            foreach ($headers as $col => $header) {
                $cell = chr(65 + $col) . '1';
                $jourSheet->setCellValue($cell, $header);
            }
            $jourSheet->getStyle('A1:J1')->applyFromArray($headerStyle);
            $jourSheet->getRowDimension(1)->setRowHeight(30);

            $jourPlannings = $plannings->filter(function ($p) use ($jour) {
                return ($p->creneau->date_pfe ?? '') === $jour;
            });

            $row = 2;
            foreach ($jourPlannings as $p) {
                $jourSheet->setCellValue("A{$row}", $p->creneau->date_pfe ?? '-');
                $jourSheet->setCellValue("B{$row}", $p->creneau->heure_debut ?? '-');
                $jourSheet->setCellValue("C{$row}", $p->creneau->heure_fin ?? '-');
                $jourSheet->setCellValue("D{$row}", $p->salle->nom ?? '-');
                $jourSheet->setCellValue("E{$row}", ($p->etudiant->nom ?? '') . ' ' . ($p->etudiant->prenom ?? ''));
                $jourSheet->setCellValue("F{$row}", $p->etudiant->filiere ?? '-');
                $jourSheet->setCellValue("G{$row}", ($p->encadrant->nom ?? '') . ' ' . ($p->encadrant->prenom ?? ''));
                $jourSheet->setCellValue("H{$row}", ($p->jury2->nom ?? '') . ' ' . ($p->jury2->prenom ?? ''));
                $jourSheet->setCellValue("I{$row}", ($p->jury3->nom ?? '') . ' ' . ($p->jury3->prenom ?? ''));
                $jourSheet->setCellValue("J{$row}", ($p->jury4->nom ?? '') . ' ' . ($p->jury4->prenom ?? ''));

                if ($row % 2 === 0) {
                    $jourSheet->getStyle("A{$row}:J{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('EBF5FB');
                }
                $row++;
            }

            if ($row > 2) {
                $jourSheet->getStyle('A1:J' . ($row - 1))->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('CCCCCC');
            }

            foreach (range('A', 'J') as $col) {
                $jourSheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        $spreadsheet->setActiveSheetIndex(0);

        $fileName = 'Planning_PFE_' . date('Y-m-d_H-i') . '.xlsx';
        $tempPath = storage_path('app/' . $fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
