<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ExcelParserService;
use App\Models\Professeur;
use App\Models\Etudiant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        // Import unifié : un seul fichier avec plusieurs feuilles
        $request->validate([
            'file_unified' => 'required|mimes:xlsx,xls',
        ]);
        
        try {
            $this->excelService->importUnifiedFile(
                $request->file('file_unified')->getRealPath()
            );
            
            // Message de succès dynamique - liste toutes les filières présentes
            $total = \App\Models\Etudiant::count();
            $parFiliere = \App\Models\Etudiant::selectRaw('filiere, count(*) as total')
                ->groupBy('filiere')
                ->pluck('total', 'filiere')
                ->toArray();
            
            $details = collect($parFiliere)
                ->map(fn($count, $filiere) => "$filiere: $count")
                ->implode(', ');
            
            $message = "Importation réussie ! Total: $total étudiants ($details)";

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Erreur lors de l'importation : " . $e->getMessage());
        }
    }

    public function reset()
    {
        try {
            Schema::disableForeignKeyConstraints();

            DB::table('plannings')->truncate();
            DB::table('etudiants')->truncate();
            DB::table('professeurs')->truncate();
            DB::table('salles')->truncate();
            DB::table('creneaux')->truncate();

            Schema::enableForeignKeyConstraints();

            return redirect()->back()->with('success', 'La base de données a été réinitialisée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la réinitialisation : ' . $e->getMessage());
        }
    }
}


