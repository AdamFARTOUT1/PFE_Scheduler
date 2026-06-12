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
        $filieres = ['ID', 'TDIA', 'GI'];

        return view('planning.index', compact('plannings', 'jours', 'salles', 'filieres'));
    }

    public function generer(\Illuminate\Http\Request $request)
    {
        set_time_limit(120); // Le scheduling peut prendre du temps avec 3 filières

        $etudiants = Etudiant::all();
        $professeurs = Professeur::all();
        $salles = Salle::all();

        if ($etudiants->isEmpty() || $professeurs->isEmpty() || $salles->isEmpty()) {
            return redirect()->route('planning.index')
                ->with('error', 'Veuillez d\'abord importer les données (étudiants, professeurs, salles).');
        }

        // Date de début et durée choisies par l'utilisateur
        $dateDebut = $request->input('date_debut', date('Y-m-d'));
        $nbJours = (int) $request->input('duree_jours', 3);
        $nbJours = max(1, min(10, $nbJours)); // Sécurité : entre 1 et 10 jours
        $dateFin = \Carbon\Carbon::parse($dateDebut)->addDays($nbJours - 1)->toDateString();

        // Heures de début et fin choisies par l'utilisateur
        $heureDebut = $request->input('heure_debut', '09:00');
        $heureFin = $request->input('heure_fin', '18:00');

        try {
            $scheduler = app(SchedulerService::class);
            $results = $scheduler->generate($etudiants, $professeurs, $salles, $dateDebut, $dateFin, $nbJours, $heureDebut, $heureFin);

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