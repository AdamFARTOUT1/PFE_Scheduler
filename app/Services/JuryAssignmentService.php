<?php

namespace App\Services;

use App\Models\Etudiant;
use Illuminate\Support\Collection;

class JuryAssignmentService
{
    /**
     * Assigne 3 jurys pour un étudiant :
     * - Si langue = 'en', le prof d'anglais est automatiquement inclus comme jury
     * - Les jurys restants sont choisis parmi les profs les moins chargés
     * - L'encadrant ne peut pas être jury
     */
    public function assign(
        Etudiant $etudiant,
        int $encadrantId,
        Collection $professeurs,
        array $existingJurys = [],
        array $planning = []
    ): array {
        // Tous les professeurs sauf l'encadrant peuvent être jury
        $candidats = $professeurs->filter(fn($prof) => $prof->id !== $encadrantId);

        // Calculer la charge actuelle (nombre de fois déjà jury)
        $charge = [];
        foreach ($professeurs as $prof) {
            $charge[$prof->id] = 0;
        }
        foreach ($existingJurys as $jury) {
            if (isset($jury[0])) $charge[$jury[0]] = ($charge[$jury[0]] ?? 0) + 1;
            if (isset($jury[1])) $charge[$jury[1]] = ($charge[$jury[1]] ?? 0) + 1;
            if (isset($jury[2])) $charge[$jury[2]] = ($charge[$jury[2]] ?? 0) + 1;
        }

        $jury = [];

        // Si l'étudiant est anglophone, inclure le prof d'anglais en priorité
        if (strtolower($etudiant->langue ?? '') === 'en') {
            $profAnglais = $candidats->first(function ($prof) {
                $specialite = strtolower($prof->specialite ?? '');
                return str_contains($specialite, 'anglais') || str_contains($specialite, 'english');
            });

            if ($profAnglais) {
                $jury[] = $profAnglais->id;
                $candidats = $candidats->filter(fn($prof) => $prof->id !== $profAnglais->id);
            }
        }

        // Combien de jurys reste-t-il à trouver ?
        $remaining = 3 - count($jury);

        if ($candidats->count() < $remaining) {
            // Pas assez pour 3, essayer avec ce qu'on a (minimum 2)
            $minRequired = max(2 - count($jury), 0);
            if ($candidats->count() < $minRequired) {
                throw new \RuntimeException("Pas assez de professeurs pour constituer un jury pour l'étudiant {$etudiant->id}");
            }
            $remaining = $candidats->count();
        }

        // Choisir les professeurs les moins chargés pour compléter le jury
        $sorted = $candidats->sortBy(fn($prof) => $charge[$prof->id] ?? 0)->values();

        for ($i = 0; $i < $remaining; $i++) {
            $jury[] = $sorted[$i]->id;
        }

        return $jury;
    }
}