<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PdfExporterService;
use App\Services\WordExporterService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function __construct(
        private PdfExporterService $pdfExporter,
        private WordExporterService $wordExporter
    ) {
    }

    /*
     * Route: GET /export/generate/{doc}/{format}
     * doc    : affectation | planning | pvs
     * format : pdf | docx
     */

    public function index()
    {
        return view('export.index');
    }

    public function generate(string $doc, string $format): BinaryFileResponse|\Illuminate\Http\Response
    {
        $allowed = ['affectation', 'planning', 'pvs'];
        $formats = ['pdf', 'docx'];

        if (!in_array($doc, $allowed) || !in_array($format, $formats)) {
            abort(404, 'Document ou format invalide.');
        }
        
        // PVs are always DOCX (zip)
        if ($doc === 'pvs') {
            return $this->wordExporter->generatePVsZip();
        }
        
        $filePath = match (true) {
            $doc === 'affectation' && $format === 'pdf' => $this->pdfExporter->generateAffectation(),
            $doc === 'affectation' && $format === 'docx' => $this->wordExporter->generateAffectation(),
            $doc === 'planning' && $format === 'pdf' => $this->pdfExporter->generatePlanning(),
            $doc === 'planning' && $format === 'docx' => $this->wordExporter->generatePlanning(),
        };

        if (!$filePath || !file_exists($filePath)) {
            abort(500, 'Erreur lors de la génération du fichier.');
        }

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        $labels = [
            'affectation' => 'Affectation_Encadrants',
            'planning' => 'Planning_Soutenances',
        ];

        $filename = $labels[$doc] . '_' . now()->format('Ymd_His') . '.' . $format; //YmdHis

        return response()->download($filePath, $filename, [
            'Content-Type' => $mimeTypes[$format],
        ]);
    }
}
