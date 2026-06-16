<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use App\Models\Professeur;
use App\Models\Etudiant;

class VerificationController extends Controller
{
    public function index()
    {
        $erreurs = [];
        $warnings = [];

        $plannings = Planning::with(['etudiant', 'encadrant', 'jury2', 'jury3', 'salle', 'creneau'])->get();

        // Vérification 1 - Chevauchement de salles
        foreach ($plannings as $p1) {
            foreach ($plannings as $p2) {
                if ($p1->id >= $p2->id) continue;

                if (
                    $p1->salle_id == $p2->salle_id &&
                    $p1->creneau_id == $p2->creneau_id
                ) {
                    $erreurs[] = [
                        'titre' => 'Chevauchement de salle',
                        'detail' => 'Salle ' . ($p1->salle->nom ?? '?') . ' — ' .
                                    ($p1->etudiant->nom ?? '?') . ' et ' .
                                    ($p2->etudiant->nom ?? '?') . ' au même créneau (' .
                                    ($p1->creneau->heure_debut ?? '?') . ')'
                    ];
                }
            }
        }

        // Vérification 2 - Prof dans 2 soutenances en meme temps
        foreach ($plannings as $p1) {
            foreach ($plannings as $p2) {
                if ($p1->id >= $p2->id) continue;
                if ($p1->creneau_id != $p2->creneau_id) continue;

                $profs1 = [$p1->encadrant_id, $p1->jury2_id, $p1->jury3_id];
                $profs2 = [$p2->encadrant_id, $p2->jury2_id, $p2->jury3_id];

                $conflit = array_intersect($profs1, $profs2);
                $conflit = array_filter($conflit);

                foreach ($conflit as $profId) {
                    $prof = Professeur::find($profId);
                    $erreurs[] = [
                        'titre' => 'Prof dans 2 soutenances simultanées',
                        'detail' => ($prof->nom ?? '?') . ' ' . ($prof->prenom ?? '') .
                                    ' est affecté à ' . ($p1->etudiant->nom ?? '?') .
                                    ' et ' . ($p2->etudiant->nom ?? '?') .
                                    ' au même horaire (' . ($p1->creneau->heure_debut ?? '?') . ')'
                    ];
                }
            }
        }

        // Vérification 3 - Pause insuffisante entre 2 soutenances du même prof (même jour)
        foreach ($plannings as $p1) {
            foreach ($plannings as $p2) {
                if ($p1->id >= $p2->id) continue;
                if (!$p1->creneau || !$p2->creneau) continue;

                // Ne comparer que les soutenances du même jour
                if ($p1->creneau->date_pfe != $p2->creneau->date_pfe) continue;

                $profs1 = [$p1->encadrant_id, $p1->jury2_id, $p1->jury3_id];
                $profs2 = [$p2->encadrant_id, $p2->jury2_id, $p2->jury3_id];

                $commun = array_filter(array_intersect($profs1, $profs2));
                if (empty($commun)) continue;

                $date = $p1->creneau->date_pfe;
                $fin1    = strtotime($date . ' ' . $p1->creneau->heure_fin);
                $debut2  = strtotime($date . ' ' . $p2->creneau->heure_debut);
                $fin2    = strtotime($date . ' ' . $p2->creneau->heure_fin);
                $debut1  = strtotime($date . ' ' . $p1->creneau->heure_debut);

                $diff1 = abs($debut2 - $fin1);
                $diff2 = abs($debut1 - $fin2);
                $diff  = min($diff1, $diff2);

                // Erreur critique : aucune pause (soutenances enchaînées directement)
                if ($diff == 0) {
                    foreach ($commun as $profId) {
                        $prof = Professeur::find($profId);
                        $erreurs[] = [
                            'titre' => 'Aucune pause entre 2 soutenances',
                            'detail' => ($prof->nom ?? '?') . ' ' . ($prof->prenom ?? '') .
                                        ' — le ' . $date .
                                        ' : soutenance à ' . ($p1->creneau->heure_debut ?? '?') .
                                        '-' . ($p1->creneau->heure_fin ?? '?') .
                                        ' puis à ' . ($p2->creneau->heure_debut ?? '?') .
                                        '-' . ($p2->creneau->heure_fin ?? '?') .
                                        ' (0 min de pause)'
                        ];
                    }
                }
                // Avertissement : pause < 1h
                elseif ($diff < 3600) {
                    foreach ($commun as $profId) {
                        $prof = Professeur::find($profId);
                        $warnings[] = [
                            'titre' => 'Moins d\'1h de pause',
                            'detail' => ($prof->nom ?? '?') . ' ' . ($prof->prenom ?? '') .
                                        ' — le ' . $date .
                                        ' : soutenance à ' . ($p1->creneau->heure_debut ?? '?') .
                                        '-' . ($p1->creneau->heure_fin ?? '?') .
                                        ' puis à ' . ($p2->creneau->heure_debut ?? '?') .
                                        '-' . ($p2->creneau->heure_fin ?? '?') .
                                        ' (intervalle : ' . round($diff / 60) . ' min)'
                        ];
                    }
                }
            }
        }
        // [MODIF] Vérification langue supprimée — colonne 'langue' retirée du modèle

        // Vérification 4 - Équilibre encadrants
        $moyenne = Etudiant::count() > 0 ? Etudiant::count() / Professeur::count() : 0;

        $profs = Professeur::withCount('etudiants')->get();
        foreach ($profs as $prof) {
            if ($prof->etudiants_count == 0) continue;

            $ecart = abs($prof->etudiants_count - $moyenne);
            if ($ecart > 2) {
                $warnings[] = [
                    'titre' => 'Déséquilibre encadrement',
                    'detail' => $prof->nom . ' ' . $prof->prenom .
                                ' encadre ' . $prof->etudiants_count .
                                ' étudiant(s) — moyenne : ' . round($moyenne, 1)
                ];
            }
        }

        // Vérification 5 - Étudiants non planifiés
        $tousEtudiants = Etudiant::all();
        $etudiantsPlanifies = $plannings->pluck('etudiant_id')->unique()->toArray();
        $etudiantsNonPlanifies = $tousEtudiants->filter(fn($e) => !in_array($e->id, $etudiantsPlanifies));

        if ($etudiantsNonPlanifies->count() > 0 && $plannings->count() > 0) {
            $nbManquants = $etudiantsNonPlanifies->count();
            $nbTotal = $tousEtudiants->count();
            $nbPlanifies = count($etudiantsPlanifies);

            $erreurs[] = [
                'titre' => "Étudiants non planifiés ($nbManquants/$nbTotal)",
                'detail' => "$nbPlanifies étudiants planifiés sur $nbTotal. " .
                            "Il manque $nbManquants étudiant(s) : " .
                            $etudiantsNonPlanifies->take(15)->map(fn($e) => $e->nom . ' ' . $e->prenom . ' (' . $e->filiere . ')')->implode(', ') .
                            ($nbManquants > 15 ? '...' : '') .
                            ". Augmentez le nombre de jours ou la plage horaire lors de la génération."
            ];
        }

        $totalOk = $plannings->count() - count($erreurs) - count($warnings);
        if($totalOk<0){
            $totalOk = 0;
        }

        return view('verification.index', compact('erreurs', 'warnings', 'totalOk', 'plannings'));
    }
}