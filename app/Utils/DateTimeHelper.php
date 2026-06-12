<?php

namespace App\Utils;

use Carbon\Carbon;

class DateTimeHelper
{
    /**
     * Génère les créneaux horaires entre startDate et endDate.
     * Si la plage horaire chevauche 12h, elle est automatiquement découpée
     * en matin (heureDebut→12:00) et après-midi (14:00→heureFin).
     */
    public function generate(
        string $startDate,
        string $endDate,
        string $heureDebut = '09:00',
        string $heureFin = '18:00',
        int $slotMinutes = 60
    ): array {
        $slots = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Déterminer les périodes en fonction de la pause déjeuner (12h-14h)
        $debut = Carbon::parse($heureDebut);
        $fin = Carbon::parse($heureFin);
        $midi = Carbon::parse('12:00');
        $aprem = Carbon::parse('14:00');

        $periods = [];

        if ($fin->lte($midi)) {
            // Tout le matin (ex: 09:00 → 11:00)
            $periods[] = ['start' => $heureDebut, 'end' => $heureFin];
        } elseif ($debut->gte($aprem)) {
            // Tout l'après-midi (ex: 14:00 → 17:00)
            $periods[] = ['start' => $heureDebut, 'end' => $heureFin];
        } elseif ($debut->gte($midi) && $debut->lt($aprem)) {
            // Début entre 12h et 14h → on décale à 14h
            $periods[] = ['start' => '14:00', 'end' => $heureFin];
        } else {
            // La plage chevauche midi → on coupe en 2 avec pause 12h-14h
            $periods[] = ['start' => $heureDebut, 'end' => '12:00'];
            if ($fin->gt($aprem)) {
                $periods[] = ['start' => '14:00', 'end' => $heureFin];
            }
        }

        while ($start->lte($end)) {
            foreach ($periods as $period) {
                $this->addSlotsForPeriod($slots, $start->toDateString(), $period['start'], $period['end'], $slotMinutes);
            }
            $start->addDay();
        }

        return $slots;
    }

    private function addSlotsForPeriod(array &$slots, string $date, string $periodStart, string $periodEnd, int $slotMinutes): void
    {
        $slotStart = Carbon::parse($date . ' ' . $periodStart);
        $periodEnd = Carbon::parse($date . ' ' . $periodEnd);

        while ($slotStart->copy()->addMinutes($slotMinutes)->lte($periodEnd)) {
            $slots[] = [
                'date'       => $date,
                'heure_debut'=> $slotStart->format('H:i'),
                'heure_fin'  => $slotStart->copy()->addMinutes($slotMinutes)->format('H:i'),
            ];
            $slotStart->addMinutes($slotMinutes);
        }
    }
}
