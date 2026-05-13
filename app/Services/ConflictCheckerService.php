<?php

namespace App\Services;

use Carbon\Carbon;

class ConflictCheckerService
{
    public function isSlotAvailable(
        array $slot,
        int $encadrantId,
        int $jury1Id,
        int $jury2Id,
        ?int $jury3Id,
        int $salleId,
        array $existingPlannings
    ): bool {
        $slotStart = Carbon::parse($slot['date'] . ' ' . $slot['heure_debut']);
        $slotEnd   = Carbon::parse($slot['date'] . ' ' . $slot['heure_fin']);

        $nouveauxProfs = array_filter([$encadrantId, $jury1Id, $jury2Id, $jury3Id]);

        foreach ($existingPlannings as $planning) {
            // 1. Même salle déjà occupée ?
            if ($planning['salle_id'] == $salleId && $planning['date'] == $slot['date']) {
                $existingStart = Carbon::parse($planning['date'] . ' ' . $planning['heure_debut']);
                $existingEnd   = Carbon::parse($planning['date'] . ' ' . $planning['heure_fin']);
                if ($slotStart < $existingEnd && $slotEnd > $existingStart) {
                    return false;
                }
            }

            // 2. Un des professeurs est déjà occupé ?
            $profsExistants = array_filter([
                $planning['encadrant_id'],
                $planning['jury1_id'],
                $planning['jury2_id'],
                $planning['jury3_id'] ?? null,
            ]);

            if (array_intersect($nouveauxProfs, $profsExistants) === []) {
                continue;
            }

            if ($planning['date'] != $slot['date']) {
                continue;
            }

            $existingStart = Carbon::parse($planning['date'] . ' ' . $planning['heure_debut']);
            $existingEnd   = Carbon::parse($planning['date'] . ' ' . $planning['heure_fin']);

            // Seulement interdire les chevauchements
            if ($slotStart < $existingEnd && $slotEnd > $existingStart) {
                return false;
            }
        }

        return true;
    }
}