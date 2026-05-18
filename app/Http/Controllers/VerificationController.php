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

        // Vérification 3 - moins de 1h de repos entre 2 soutenances du même prof
        foreach ($plannings as $p1) {
            foreach ($plannings as $p2) {
                if ($p1->id >= $p2->id) continue;
                if (!$p1->creneau || !$p2->creneau) continue;

                $profs1 = [$p1->encadrant_id, $p1->jury2_id, $p1->jury3_id];
                $profs2 = [$p2->encadrant_id, $p2->jury2_id, $p2->jury3_id];

                $commun = array_filter(array_intersect($profs1, $profs2));
                if (empty($commun)) continue;

                $fin1    = strtotime($p1->creneau->heure_fin);
                $debut2  = strtotime($p2->creneau->heure_debut);
                $fin2    = strtotime($p2->creneau->heure_fin);
                $debut1  = strtotime($p1->creneau->heure_debut);

                $diff1 = abs($debut2 - $fin1);
                $diff2 = abs($debut1 - $fin2);
                $diff  = min($diff1, $diff2);

                if ($diff < 3600 && $diff > 0) {
                    foreach ($commun as $profId) {
                        $prof = Professeur::find($profId);
                        $warnings[] = [
                            'titre' => 'Moins d\'1h de repos',
                            'detail' => ($prof->nom ?? '?') . ' ' . ($prof->prenom ?? '') .
                                        ' — soutenance à ' . ($p1->creneau->heure_debut ?? '?') .
                                        ' et à ' . ($p2->creneau->heure_debut ?? '?') .
                                        ' (intervalle : ' . round($diff / 60) . ' min)'
                        ];
                    }
                }
            }
        }
        foreach ($plannings as $p) {
    if (!$p->etudiant) continue;
    
    if ($p->etudiant->langue === 'EN') {
        $profs = [
            $p->encadrant,
            $p->jury2,
            $p->jury3
        ];
        
        $aAnglais = false;
        foreach ($profs as $prof) {
            if ($prof && strtolower($prof->specialite) === 'anglais') {
                $aAnglais = true;
                break;
            }
        }
        
        if (!$aAnglais) {
            $warnings[] = [
                'titre' => 'PFE anglais sans prof anglais',
                'detail' => ($p->etudiant->nom ?? '?') . ' ' . 
                            ($p->etudiant->prenom ?? '') . 
                            ' — soutenance en anglais mais aucun prof d\'anglais dans le jury'
            ];
        }
    }
}

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

        $totalOk = $plannings->count() - count($erreurs) - count($warnings);

        return view('verification.index', compact('erreurs', 'warnings', 'totalOk', 'plannings'));
    }
}