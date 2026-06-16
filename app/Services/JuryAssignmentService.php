<?php

namespace App\Services;

use App\Models\Etudiant;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class JuryAssignmentService
{
    /**
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

        // Filtrer par disponibilité dans le créneau actuel
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
        }

        // [MODIF] Logique langue supprimée — on sélectionne toujours 2 jurys les moins chargés
        $remaining = 2;

        if ($candidats->count() < $remaining) {
            $remaining = $candidats->count();
        }

        // Choisir les professeurs les moins chargés pour compléter le jury
        $sorted = $candidats->sortBy(fn($prof) => $charge[$prof->id] ?? 0)->values();

        $jury = [];
        for ($i = 0; $i < $remaining; $i++) {
            $jury[] = $sorted[$i]->id;
        }

        return $jury;
    }

    /**
     * Vérifie qu'un prof est disponible dans un créneau (pas de chevauchement)
     * Note : la contrainte de pause de 1h est vérifiée dans le module Vérification
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
            ]);

            if (!in_array($profId, $profsInPlanning)) continue;

            $existStart = Carbon::parse($p['date'] . ' ' . $p['heure_debut']);
            $existEnd   = Carbon::parse($p['date'] . ' ' . $p['heure_fin']);

            // Chevauchement ?
            if ($slotStart < $existEnd && $slotEnd > $existStart) {
                return false;
            }
        }

        return true;
    }
}