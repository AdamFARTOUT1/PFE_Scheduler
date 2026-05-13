<?php

namespace App\Http\Controllers;

use App\Models\Professeur;
use App\Models\Etudiant;
use App\Models\Planning;
use App\Models\Salle;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEtudiants = Etudiant::count();
        $totalProfs = Professeur::count();
        $totalSoutenances = Planning::count();
        $totalSalles = Salle::count();

        // Étudiants par professeur encadrant
        $etudiantsParProf = Professeur::withCount('etudiants')->get();
        $maxEtudiants = $etudiantsParProf->max('etudiants_count') ?? 1;

        // Étudiants par filière (pas soutenances qui sont vides)
        $etudiantsParFiliere = Etudiant::selectRaw('filiere, count(*) as total')
            ->groupBy('filiere')
            ->get();

        // Soutenances par professeur (comme encadrant)
        $soutenancesParProf = Professeur::withCount('planningsAsEncadrant')->get();
        $maxSoutenances = $soutenancesParProf->max('plannings_as_encadrant_count') ?? 1;

        return view('dashboard.index', compact(
            'totalEtudiants',
            'totalProfs',
            'totalSoutenances',
            'totalSalles',
            'etudiantsParProf',
            'maxEtudiants',
            'etudiantsParFiliere',
            'soutenancesParProf',
            'maxSoutenances'
        ));
    }
}