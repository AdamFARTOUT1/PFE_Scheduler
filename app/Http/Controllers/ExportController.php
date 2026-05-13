<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use App\Models\Salle;
use App\Models\Creneau;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ExportController extends Controller
{
    public function index()
    {
        $plannings = Planning::with(['etudiant', 'encadrant', 'jury2', 'jury3', 'jury4', 'salle', 'creneau'])
            ->get();

        $jours = $plannings->pluck('creneau.date_pfe')->unique()->filter()->values();
        $salles = Salle::all();
        $filieres = $plannings->pluck('etudiant.filiere')->unique()->filter()->values();

        return view('export.index', compact('plannings', 'jours', 'salles', 'filieres'));
    }

    public function download()
    {
        $plannings = Planning::with(['etudiant', 'encadrant', 'jury2', 'jury3', 'jury4', 'salle', 'creneau'])
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

            // Alterner les couleurs de lignes
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

        // ── Feuille 2 : Par Jour ──
        $jours = $plannings->pluck('creneau.date_pfe')->unique()->filter()->sort();
        foreach ($jours as $jour) {
            $jourSheet = $spreadsheet->createSheet();
            $jourSheet->setTitle(substr($jour, 0, 31));

            // En-têtes
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

        // Revenir à la première feuille
        $spreadsheet->setActiveSheetIndex(0);

        // Générer le fichier
        $fileName = 'Planning_PFE_' . date('Y-m-d_H-i') . '.xlsx';
        $tempPath = storage_path('app/' . $fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
