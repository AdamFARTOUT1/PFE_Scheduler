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

        $etudiantsParProf = Professeur::withCount('etudiants')->get();
        $maxEtudiants = $etudiantsParProf->max('etudiants_encadres_count');

        $soutenancesParFiliere = Etudiant::selectRaw('filiere, count(*) as total')
            ->groupBy('filiere')
            ->get();

        $soutenancesParProf = Professeur::withCount('planningsAsEncadrant')->get();
        $maxSoutenances = $soutenancesParProf->max('plannings_count');

        return view('dashboard.index', compact(
            'totalEtudiants',
            'totalProfs',
            'totalSoutenances',
            'totalSalles',
            'etudiantsParProf',
            'maxEtudiants',
            'soutenancesParFiliere',
            'soutenancesParProf',
            'maxSoutenances'
        ));
    }
}