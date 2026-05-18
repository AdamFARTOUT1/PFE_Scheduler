<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use App\Models\Salle;
use App\Models\Creneau;
use App\Models\Etudiant;
use App\Models\Professeur;
use App\Services\SchedulerService;

class PlanningController extends Controller
{
    public function index()
    {
        $plannings = Planning::select('plannings.*')
            ->join('creneaux', 'plannings.creneau_id', '=', 'creneaux.id')
            ->with(['etudiant', 'encadrant', 'jury2', 'jury3', 'salle', 'creneau'])
            ->orderBy('creneaux.date_pfe')
            ->orderBy('creneaux.heure_debut')
            ->get();

        $jours = $plannings->pluck('creneau.date_pfe')->unique()->filter()->values();
        $salles = Salle::all();
        $filieres = ['ID', 'TDIA'];

        return view('planning.index', compact('plannings', 'jours', 'salles', 'filieres'));
    }

    public function generer(\Illuminate\Http\Request $request)
    {
        $etudiants = Etudiant::all();
        $professeurs = Professeur::all();
        $salles = Salle::all();

        if ($etudiants->isEmpty() || $professeurs->isEmpty() || $salles->isEmpty()) {
            return redirect()->route('planning.index')
                ->with('error', 'Veuillez d\'abord importer les données (étudiants, professeurs, salles).');
        }

        // Date de début choisie par l'utilisateur, divisée sur 3 jours
        $dateDebut = $request->input('date_debut', date('Y-m-d'));
        $dateFin = \Carbon\Carbon::parse($dateDebut)->addDays(2)->toDateString();

        try {
            $scheduler = app(SchedulerService::class);
            $results = $scheduler->generate($etudiants, $professeurs, $salles, $dateDebut, $dateFin);

            // Vider le planning existant (plannings d'abord car il référence creneaux)
            Planning::query()->delete();
            Creneau::query()->delete();

            foreach ($results as $item) {
                // Créer ou retrouver le créneau
                $creneau = Creneau::firstOrCreate(
                    [
                        'date_pfe'    => $item['date'],
                        'heure_debut' => $item['heure_debut'],
                        'heure_fin'   => $item['heure_fin'],
                    ]
                );

                Planning::create([
                    'etudiant_id'  => $item['etudiant_id'],
                    'encadrant_id' => $item['encadrant_id'],
                    'jury2_id'     => $item['jury1_id'],
                    'jury3_id'     => $item['jury2_id'],
                    'salle_id'     => $item['salle_id'],
                    'creneau_id'   => $creneau->id,
                ]);
            }

            return redirect()->route('planning.index')
                ->with('success', 'Planning généré avec succès ! (' . count($results) . ' soutenances)');
        } catch (\Exception $e) {
            \Log::error('Erreur génération planning: ' . $e->getMessage());
            return redirect()->route('planning.index')
                ->with('error', 'Erreur lors de la génération : ' . $e->getMessage());
        }
    }
}