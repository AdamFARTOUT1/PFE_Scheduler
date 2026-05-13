<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use App\Models\Salle;
use App\Models\Creneau;
use App\Services\SchedulerService;

class PlanningController extends Controller
{
    public function index()
    {
        $plannings = Planning::with(['etudiant', 'encadrant', 'jury2', 'jury3', 'jury4', 'salle', 'creneau'])
            ->get();

        $jours = $plannings->pluck('creneau.jour')->unique()->filter()->values();
        $salles = Salle::all();
        $filieres = ['ID', 'TDAI'];

        return view('planning.index', compact('plannings', 'jours', 'salles', 'filieres'));
    }

    public function generer()
    {
        app(SchedulerService::class)->run();
        return redirect()->route('planning.index')->with('success', 'Planning généré avec succès !');
    }
}