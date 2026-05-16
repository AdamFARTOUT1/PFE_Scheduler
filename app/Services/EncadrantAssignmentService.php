<?php

namespace App\Services;

use Illuminate\Support\Collection;

class EncadrantAssignmentService
{
    /**
     * Retourne l'encadrant déjà attribué à chaque étudiant.
     * Si un étudiant n'a pas d'encadrant, on lui assigne le professeur le moins chargé.
     */
    public function assign(Collection $etudiants, Collection $professeurs, int $maxParProf = 5): array
    {
        $assignment = [];
        // Exclure les profs d'anglais de la liste des encadrants
        $professeursEligibles = $professeurs->filter(function ($prof) {
            $spec = strtolower(trim($prof->specialite ?? ''));
            return !str_contains($spec, 'anglais') && !str_contains($spec, 'english');
        });

        $counts = $professeursEligibles->pluck('id')->mapWithKeys(fn($id) => [$id => 0])->toArray();

        // D'abord, utiliser les encadrants déjà fixés en base
        foreach ($etudiants as $etudiant) {
            if ($etudiant->professeur_ID && isset($counts[$etudiant->professeur_ID])) {
                $assignment[$etudiant->id] = $etudiant->professeur_ID;
                $counts[$etudiant->professeur_ID]++;
            }
        }

        // Ensuite, attribuer un encadrant à ceux qui n'en ont pas (si nécessaire)
        foreach ($etudiants as $etudiant) {
            if (isset($assignment[$etudiant->id])) continue;

            // Attribuer en priorité à ceux qui ont le moins d'étudiants (pour que tout le monde en ait)
            $candidats = $professeursEligibles->filter(fn($prof) => $counts[$prof->id] < $maxParProf);

            if ($candidats->isEmpty()) {
                throw new \RuntimeException("Aucun professeur disponible pour l'étudiant {$etudiant->id}");
            }

            $chosen = $candidats->sortBy(fn($prof) => $counts[$prof->id])->first();
            $assignment[$etudiant->id] = $chosen->id;
            $counts[$chosen->id]++;
        }

        return $assignment;
    }
}