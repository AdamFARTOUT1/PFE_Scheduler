<?php

namespace App\Services;

use App\Models\Etudiant;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class JuryAssignmentService
{
    /**
     * Assigne 3 jurys pour un étudiant :
     * - Filtre les profs disponibles dans le créneau (pas de conflit horaire / 1h de pause)
     * - Si langue = 'en', le prof d'anglais est automatiquement inclus comme jury
     * - Les jurys restants sont choisis parmi les profs les moins chargés
     * - L'encadrant ne peut pas être jury
     */
    public function assign(
        Etudiant $etudiant,
        int $encadrantId,
        Collection $professeurs,
        array $existingJurys = [],
        array $planning = [],
        ?array $currentSlot = null
    ): array {
        // Tous les professeurs sauf l'encadrant
        $candidats = $professeurs->filter(fn($prof) => $prof->id !== $encadrantId);

        // Filtrer par disponibilité dans le créneau actuel (1h de pause obligatoire)
        if ($currentSlot !== null) {
            $candidats = $candidats->filter(function ($prof) use ($currentSlot, $planning) {
                return $this->isProfAvailable($prof->id, $currentSlot, $planning);
            });
        }

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

        // Si l'étudiant est anglophone, on prépare le prof d'anglais pour l'ajouter en dernier
        $profAnglaisId = null;
        if (strtolower($etudiant->langue ?? '') === 'en') {
            $profAnglais = $candidats->first(function ($prof) {
                $specialite = strtolower($prof->specialite ?? '');
                return str_contains($specialite, 'anglais') || str_contains($specialite, 'english');
            });

            if ($profAnglais) {
                $profAnglaisId = $profAnglais->id;
                $candidats = $candidats->filter(fn($prof) => $prof->id !== $profAnglais->id);
            }
        }

        // Combien de jurys reste-t-il à trouver ? (2 si on a un prof d'anglais, sinon 3)
        $remaining = $profAnglaisId ? 2 : 3;

        if ($candidats->count() < $remaining) {
            // Pas assez pour le quota, essayer avec ce qu'on a
            $remaining = $candidats->count();
        }

        // Choisir les professeurs les moins chargés pour compléter le jury
        $sorted = $candidats->sortBy(fn($prof) => $charge[$prof->id] ?? 0)->values();

        for ($i = 0; $i < $remaining; $i++) {
            $jury[] = $sorted[$i]->id;
        }

        // Ajouter le prof d'anglais en dernier pour qu'il soit jury4_id (le 4eme)
        if ($profAnglaisId) {
            $jury[] = $profAnglaisId;
        }

        return $jury;
    }

    /**
     * Vérifie qu'un prof est disponible dans un créneau (pas de chevauchement + 1h de pause)
     */
    private function isProfAvailable(int $profId, array $slot, array $planning): bool
    {
        $slotStart = Carbon::parse($slot['date'] . ' ' . $slot['heure_debut']);
        $slotEnd   = Carbon::parse($slot['date'] . ' ' . $slot['heure_fin']);

        foreach ($planning as $p) {
            if ($p['date'] != $slot['date']) continue;

            // Ce prof est-il impliqué dans cette soutenance ?
            $profsInPlanning = array_filter([
                $p['encadrant_id'],
                $p['jury1_id'],
                $p['jury2_id'],
                $p['jury3_id'] ?? null,
            ]);

            if (!in_array($profId, $profsInPlanning)) continue;

            $existStart = Carbon::parse($p['date'] . ' ' . $p['heure_debut']);
            $existEnd   = Carbon::parse($p['date'] . ' ' . $p['heure_fin']);

            // Chevauchement ?
            if ($slotStart < $existEnd && $slotEnd > $existStart) {
                return false;
            }

            // Vérifier la pause de 1h
            if ($slotStart >= $existEnd) {
                $gap = $existEnd->diffInMinutes($slotStart);
            } else {
                $gap = $slotEnd->diffInMinutes($existStart);
            }

            if ($gap < 60) {
                return false;
            }
        }

        return true;
    }
}