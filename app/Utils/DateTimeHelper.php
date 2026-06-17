<?php

namespace App\Utils;

use Carbon\Carbon;

class DateTimeHelper
{
    /**
     * Génère les créneaux horaires entre startDate et endDate.
     *
     * La pause est configurable via $pauseDebut et $pauseFin.
     * Si ces paramètres sont vides ou nuls, aucune pause n'est appliquée.
     * Sinon, la plage est découpée autour de la pause (ex: pause déjeuner 12:00-14:00).
     *
     * @param string $startDate     Date de début (Y-m-d)
     * @param string $endDate       Date de fin (Y-m-d)
     * @param string $heureDebut    Heure de début du jour (HH:MM)
     * @param string $heureFin      Heure de fin du jour (HH:MM)
     * @param int    $slotMinutes   Durée d'un créneau en minutes
     * @param string $pauseDebut    Heure de début de la pause (HH:MM), vide = pas de pause
     * @param string $pauseFin      Heure de fin de la pause (HH:MM), vide = pas de pause
     */
    public function generate(
        string $startDate,
        string $endDate,
        string $heureDebut = '09:00',
        string $heureFin = '18:00',
        int $slotMinutes = 60,
        string $pauseDebut = '',
        string $pauseFin = ''
    ): array {
        $slots = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $debut = Carbon::parse($heureDebut);
        $fin = Carbon::parse($heureFin);

        $periods = [];

        // Pause configurable
        $hasPause = !empty($pauseDebut) && !empty($pauseFin);

        if ($hasPause) {
            $pDebut = Carbon::parse($pauseDebut);
            $pFin = Carbon::parse($pauseFin);

            if ($fin->lte($pDebut)) {
                // Toute la plage est avant la pause
                $periods[] = ['start' => $heureDebut, 'end' => $heureFin];
            } elseif ($debut->gte($pFin)) {
                // Toute la plage est après la pause
                $periods[] = ['start' => $heureDebut, 'end' => $heureFin];
            } elseif ($debut->gte($pDebut) && $debut->lt($pFin)) {
                // Début pendant la pause → on décale à la fin de la pause
                if ($fin->gt($pFin)) {
                    $periods[] = ['start' => $pauseFin, 'end' => $heureFin];
                }
                // sinon pas de créneau possible (toute la plage est dans la pause)
            } else {
                // La plage chevauche la pause → on coupe en 2
                $periods[] = ['start' => $heureDebut, 'end' => $pauseDebut];
                if ($fin->gt($pFin)) {
                    $periods[] = ['start' => $pauseFin, 'end' => $heureFin];
                }
            }
        } else {
            // Pas de pause → plage continue
            $periods[] = ['start' => $heureDebut, 'end' => $heureFin];
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
