<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ExcelParserService;
use App\Models\Professeur;
use App\Models\Etudiant;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    protected $excelService;
    
    public function __construct(ExcelParserService $service)
    {
        $this->excelService = $service;
    }

    public function index ()
    {
        $stats = [
            'total_etudiants'=>Etudiant::count(),
            'total_professeurs'=>Professeur::count(),
            'par_filiere'=>Etudiant::select('filiere',DB::raw('count(*) as total'))->groupBy('filiere')->get(),
            'encadrement_pfe'=>Professeur::withCount('etudiants')->get(),];

        return view('import.index',compact('stats'));
    }

    public function store(Request $request)
    {
        $filiere = $request->input('filiere');
        
        // Import des professeurs uniquement
        if ($filiere === 'PROFS_ONLY') {
            $request->validate([
                'file_profs' => 'required|mimes:xlsx,xls',
            ]);
            try {
                $this->excelService->importProfesseurs($request->file('file_profs')->getRealPath());
                return redirect()->back()->with('success', 'Professeurs importés avec succès !');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Erreur lors de l\'importation des professeurs : ' . $e->getMessage());
            }
        }
        
        // Import des étudiants avec filière
        $request->validate([
            'filiere' => 'required|in:ID,TDIA',
            'file_etudiants' => 'required|mimes:xlsx,xls',
        ]);
        try {
            $this->excelService->importEtudiantsMultiSheets(
                $request->file('file_etudiants')->getRealPath(),
                $filiere
            );

            $anomalies = $this->excelService->checkConformity();

            if (!empty($anomalies)) {
                return redirect()->back()->with([
                    'success' => "Étudiants $filiere importés avec succès.",
                    'anomalies' => $anomalies
                ]);
            }

            return redirect()->back()->with('success', "Importation $filiere réussie et conforme aux règles !");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Erreur lors de l'importation de la filière $filiere : " . $e->getMessage());
        }
    }
}

