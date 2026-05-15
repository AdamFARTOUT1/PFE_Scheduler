<?php

namespace App\Services;

use App\Models\Etudiant;
use App\Models\Professeur;
use App\Models\Salle;
use App\Utils\DateTimeHelper;
use Illuminate\Support\Collection;

class SchedulerService
{
    private DateTimeHelper $dateTimeHelper;
    private ConflictCheckerService $conflictChecker;
    private EncadrantAssignmentService $encadrantAssigner;
    private JuryAssignmentService $juryAssigner;

    public function __construct()
    {
        $this->dateTimeHelper = new DateTimeHelper();
        $this->conflictChecker = new ConflictCheckerService();
        $this->encadrantAssigner = new EncadrantAssignmentService();
        $this->juryAssigner = new JuryAssignmentService();
    }

    public function generate(
        Collection $etudiants,
        Collection $professeurs,
        Collection $salles,
        string $dateDebut = '2026-05-12',
        string $dateFin = '2026-05-15'
    ): array {
        $encadrants = $this->encadrantAssigner->assign($etudiants, $professeurs);
        $creneaux = $this->dateTimeHelper->generate($dateDebut, $dateFin);

        $orders = $this->generateStudentOrders($etudiants);

        foreach ($orders as $order) {
            try {
                $planning = [];
                $juryAttributions = [];

                // Calculer le nombre max de soutenances par jour et par créneau pour bien répartir
                $nbJours = 3;
                $maxParJour = (int) ceil(count($order) / $nbJours);
                $maxParCreneau = (int) ceil(count($order) / count($creneaux));
                
                // On peut ajouter une petite marge de tolérance sur le créneau
                if ($maxParCreneau < 4) {
                    $maxParCreneau = 4;
                }

                $compteurParJour = [];
                $compteurParCreneau = [];

                $unplacedEtudiants = $order;

                foreach ($creneaux as $creneau) {
                    $dateJour = $creneau['date'];
                    $slotKey = $creneau['date'] . ' ' . $creneau['heure_debut'];

                    foreach ($unplacedEtudiants as $k => $etudiant) {
                        // Vérifier le quota du jour
                        if (($compteurParJour[$dateJour] ?? 0) >= $maxParJour) {
                            break; // Journée pleine, on passe au créneau/jour suivant
                        }

                        // Vérifier le quota par créneau
                        if (($compteurParCreneau[$slotKey] ?? 0) >= $maxParCreneau) {
                            break; // Créneau plein
                        }

                        $encadrantId = $encadrants[$etudiant->id];

                        $salleLibre = $this->trouverSalleLibre($creneau, $planning, $salles);
                        if ($salleLibre === null) break; // Plus de salles libres ce créneau

                        // Vérifier que l'encadrant est disponible dans ce créneau
                        if (!$this->isProfDisponible($encadrantId, $creneau, $planning)) {
                            continue; // L'encadrant est occupé (ou pause), on essaie l'étudiant suivant
                        }

                        try {
                            $jury = $this->juryAssigner->assign(
                                $etudiant,
                                $encadrantId,
                                $professeurs,
                                $juryAttributions,
                                $planning,
                                $creneau
                            );
                        } catch (\RuntimeException $e) {
                            continue; // Impossible de former un jury, on essaie l'étudiant suivant
                        }

                        $ok = $this->conflictChecker->isSlotAvailable(
                            $creneau,
                            $encadrantId,
                            $jury[0],
                            $jury[1],
                            $jury[2] ?? null,
                            $salleLibre->id,
                            $planning
                        );

                        if ($ok) {
                            $planning[] = [
                                'etudiant_id'  => $etudiant->id,
                                'encadrant_id' => $encadrantId,
                                'jury1_id'     => $jury[0],
                                'jury2_id'     => $jury[1],
                                'jury3_id'     => $jury[2] ?? null,
                                'salle_id'     => $salleLibre->id,
                                'date'         => $creneau['date'],
                                'heure_debut'  => $creneau['heure_debut'],
                                'heure_fin'    => $creneau['heure_fin'],
                            ];
                            $juryAttributions[$etudiant->id] = $jury;
                            $compteurParJour[$dateJour] = ($compteurParJour[$dateJour] ?? 0) + 1;
                            $compteurParCreneau[$slotKey] = ($compteurParCreneau[$slotKey] ?? 0) + 1;
                            
                            unset($unplacedEtudiants[$k]); // Étudiant placé !
                        }
                    }
                }

                if (count($unplacedEtudiants) > 0) {
                    throw new \RuntimeException("Impossible de placer tous les étudiants");
                }

                return $planning;

            } catch (\RuntimeException $e) {
                continue;
            }
        }

        throw new \RuntimeException("Impossible de générer un planning complet.");
    }

    private function generateStudentOrders(Collection $etudiants): array
    {
        $professeurs = \App\Models\Professeur::all();
        $encadrants = $this->encadrantAssigner->assign($etudiants, $professeurs);
        
        // Séparer par filière
        $etudiantsID = $etudiants->filter(fn($e) => strtoupper($e->filiere) === 'ID')->values();
        $etudiantsTDIA = $etudiants->filter(fn($e) => strtoupper($e->filiere) !== 'ID')->values();
        
        // Créer plusieurs ordres en intercalant ID et TDIA
        $orders = [];
        for ($i = 0; $i < 50; $i++) {
            $idCopy = $etudiantsID->shuffle()->values()->all();
            $tdiaCopy = $etudiantsTDIA->shuffle()->values()->all();
            
            // Intercaler : 1 ID, 1 TDIA, 1 ID, 1 TDIA...
            $order = [];
            $idIdx = 0;
            $tdiaIdx = 0;
            $totalID = count($idCopy);
            $totalTDIA = count($tdiaCopy);
            
            while ($idIdx < $totalID || $tdiaIdx < $totalTDIA) {
                if ($idIdx < $totalID) {
                    $order[] = $idCopy[$idIdx++];
                }
                if ($tdiaIdx < $totalTDIA) {
                    $order[] = $tdiaCopy[$tdiaIdx++];
                }
            }
            
            $orders[] = $order;
        }
        
        return $orders;
    }

    private function trouverSalleLibre(array $creneau, array $planning, Collection $salles): ?Salle
    {
        foreach ($salles as $salle) {
            $occupee = false;
            foreach ($planning as $p) {
                if (
                    $p['salle_id'] == $salle->id &&
                    $p['date'] == $creneau['date'] &&
                    !(
                        $creneau['heure_fin'] <= $p['heure_debut'] ||
                        $creneau['heure_debut'] >= $p['heure_fin']
                    )
                ) {
                    $occupee = true;
                    break;
                }
            }
            if (!$occupee) {
                return $salle;
            }
        }
        return null;
    }

    /**
     * Vérifie qu'un prof est disponible dans un créneau (pas de chevauchement + 1h de pause)
     */
    private function isProfDisponible(int $profId, array $slot, array $planning): bool
    {
        $slotStart = \Carbon\Carbon::parse($slot['date'] . ' ' . $slot['heure_debut']);
        $slotEnd   = \Carbon\Carbon::parse($slot['date'] . ' ' . $slot['heure_fin']);

        foreach ($planning as $p) {
            if ($p['date'] != $slot['date']) continue;

            $profsInPlanning = array_filter([
                $p['encadrant_id'], $p['jury1_id'], $p['jury2_id'], $p['jury3_id'] ?? null,
            ]);

            if (!in_array($profId, $profsInPlanning)) continue;

            $existStart = \Carbon\Carbon::parse($p['date'] . ' ' . $p['heure_debut']);
            $existEnd   = \Carbon\Carbon::parse($p['date'] . ' ' . $p['heure_fin']);

            // Chevauchement
            if ($slotStart < $existEnd && $slotEnd > $existStart) {
                return false;
            }

            // Pause 1h obligatoire
            $gap = $slotStart >= $existEnd ? $existEnd->diffInMinutes($slotStart) : $slotEnd->diffInMinutes($existStart);

            if ($gap < 60) {
                return false;
            }
        }

        return true;
    }
}