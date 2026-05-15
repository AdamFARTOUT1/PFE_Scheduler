<?php

namespace App\Services;

use App\Models\Planning;
use App\Models\Professeur;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfExporterService
{
    private string $outputDir;

    public function __construct()
    {
        $this->outputDir = storage_path('app/outputs');
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0775, true);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    //  AFFECTATION — correspond exactement au PDF exemple
    //  Format : Encadrant | Etudiant 1 | Etudiant 2 | Etudiant 3 ...
    //  Groupé par filière (ID puis TDIA)
    // ─────────────────────────────────────────────────────────────────
    public function generateAffectation(): string
    {
        $professeurs = Professeur::with('etudiants')->orderBy('nom')->get()
            ->filter(fn($p) => $p->etudiants->count() > 0);

        $html = $this->buildAffectationHtml($professeurs);
        return $this->renderToPdf($html, 'affectation.pdf', 'landscape');
    }

    // ─────────────────────────────────────────────────────────────────
    //  PLANNING — correspond exactement au PDF exemple
    //  Format : ID | Encadrant | Jury 1 | Jury 2 | Date | Heure | Salle | Nom | Prénom
    // ─────────────────────────────────────────────────────────────────
    public function generatePlanning(): string
    {
        $plannings = Planning::with(['etudiant', 'encadrant', 'jury2', 'jury3', 'creneau', 'salle'])
            ->join('creneaux', 'plannings.creneau_id', '=', 'creneaux.id')
            ->orderBy('creneaux.date_pfe')
            ->orderBy('creneaux.heure_debut')
            ->select('plannings.*')
            ->get();

        $html = $this->buildPlanningHtml($plannings);
        return $this->renderToPdf($html, 'planning.pdf', 'landscape');
    }

    // ─────────────────────────────────────────────────────────────────
    //  HTML — AFFECTATION
    // ─────────────────────────────────────────────────────────────────
    private function buildAffectationHtml($professeurs): string
    {
        // Calculer le nombre max d'étudiants par encadrant pour les colonnes
        $maxEtudiants = $professeurs->max(fn($p) => $p->etudiants->count());
        $maxEtudiants = max($maxEtudiants, 3); // minimum 3 colonnes

        // En-têtes colonnes étudiants
        $etudiantHeaders = '';
        for ($i = 1; $i <= $maxEtudiants; $i++) {
            $etudiantHeaders .= "<th>Etudiant {$i}</th>";
        }

        // Construire les sections par filière
        $filieres = ['ID', 'TDIA'];
        $sections = '';

        foreach ($filieres as $filiere) {
            // Encadrants qui ont au moins 1 étudiant de cette filière
            $profsFiliere = $professeurs->filter(
                fn($p) => $p->etudiants->where('filiere', $filiere)->count() > 0
            );

            if ($profsFiliere->isEmpty())
                continue;

            $rows = '';
            foreach ($profsFiliere as $prof) {
                $etudiantsFiliere = $prof->etudiants->where('filiere', $filiere)->values();
                $cols = '';
                for ($i = 0; $i < $maxEtudiants; $i++) {
                    if (isset($etudiantsFiliere[$i])) {
                        $e = $etudiantsFiliere[$i];
                        $cols .= "<td>" . strtoupper($e->nom) . " " . strtoupper($e->prenom) . "</td>";
                    } else {
                        $cols .= "<td class='vide'>—</td>";
                    }
                }
                $rows .= "
                <tr>
                    <td class='encadrant'>{$prof->nom} {$prof->prenom}</td>
                    {$cols}
                </tr>";
            }

            $sections .= "
            <div class='filiere-section'>
                <div class='filiere-titre'>Filière {$filiere}</div>
                <table>
                    <thead>
                        <tr>
                            <th rowspan='2' style='vertical-align:middle; width:180px;'>Encadrant</th>
                            <th colspan='{$maxEtudiants}'>Etudiants encadrés</th>
                        </tr>
                        <tr>
                            {$etudiantHeaders}
                        </tr>
                    </thead>
                    <tbody>
                        {$rows}
                    </tbody>
                </table>
            </div>";
        }

        $date = now()->format('d/m/Y');

        return <<<HTML
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: DejaVu Sans, Arial, sans-serif;
                    font-size: 10px;
                    color: #111;
                    margin: 15px 20px;
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                    border-bottom: 2px solid #003580;
                    padding-bottom: 10px;
                }
                .header .logo-line {
                    font-size: 13px;
                    font-weight: bold;
                    color: #003580;
                }
                .header .dept {
                    font-size: 11px;
                    color: #333;
                }
                .header .titre {
                    font-size: 13px;
                    font-weight: bold;
                    margin: 6px 0 2px;
                    color: #111;
                }
                .header .annee {
                    font-size: 10px;
                    color: #555;
                }
                .filiere-section {
                    margin-bottom: 24px;
                    page-break-inside: avoid;
                }
                .filiere-titre {
                    background: #003580;
                    color: #fff;
                    font-weight: bold;
                    font-size: 11px;
                    padding: 5px 10px;
                    margin-bottom: 0;
                    border-radius: 3px 3px 0 0;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    border: 1px solid #bbb;
                }
                thead th {
                    background: #d0d8e8;
                    color: #111;
                    font-weight: bold;
                    font-size: 9px;
                    padding: 5px 4px;
                    border: 1px solid #aaa;
                    text-align: center;
                }
                tbody tr:nth-child(even) { background: #f4f6fb; }
                td {
                    padding: 5px 6px;
                    border: 1px solid #ccc;
                    vertical-align: middle;
                }
                td.encadrant {
                    font-weight: bold;
                    background: #eef2ff;
                    white-space: nowrap;
                }
                td.vide { color: #bbb; text-align: center; }
                .footer {
                    text-align: right;
                    font-size: 8px;
                    color: #999;
                    margin-top: 12px;
                    border-top: 1px solid #ddd;
                    padding-top: 4px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo-line">Ecole Nationale des Sciences Appliquées - Al Hoceima</div>
                <div class="dept">Département Mathématiques et Informatique</div>
                <div class="titre">Affectation des encadrants de Projet de Fin d'Etude</div>
                <div class="annee">Année Universitaire 2025/2026</div>
            </div>

            {$sections}

            <div class="footer">Généré le {$date} — PFE Scheduler ENSA Al-Hoceima</div>
        </body>
        </html>
        HTML;
    }

    // ─────────────────────────────────────────────────────────────────
    //  HTML — PLANNING
    // ─────────────────────────────────────────────────────────────────
    private function buildPlanningHtml($plannings): string
    {
        $rows = '';
        $id = 1;

        foreach ($plannings as $p) {
            // Format heure : "09:00:00" → "9h"
            $heure = intval(substr($p->creneau->heure_debut, 0, 2)) . 'h';

            // Format date : "2025-06-23" → "23/06/2025"
            $date = \Carbon\Carbon::parse($p->creneau->date_pfe)->format('d/m/Y');

            $encadrant = e($p->encadrant->nom . ' ' . $p->encadrant->prenom);
            $jury1 = e($p->jury2->nom . ' ' . $p->jury2->prenom);   // Jury 2 du tableau = membre jury 1
            $jury2 = e($p->jury3->nom . ' ' . $p->jury3->prenom);   // Jury 3 du tableau = membre jury 2
            $salle = e($p->salle->nom);
            $nom = strtoupper(e($p->etudiant->nom));
            $prenom = strtoupper(e($p->etudiant->prenom));

            $bg = ($id % 2 === 0) ? '#f4f6fb' : '#ffffff';

            $rows .= "
            <tr style='background:{$bg};'>
                <td class='center'>{$id}</td>
                <td>{$encadrant}</td>
                <td>{$jury1}</td>
                <td>{$jury2}</td>
                <td class='center'>{$date}</td>
                <td class='center'>{$heure}</td>
                <td class='center'>{$salle}</td>
                <td>{$nom}</td>
                <td>{$prenom}</td>
            </tr>";
            $id++;
        }

        $date = now()->format('d/m/Y');

        return <<<HTML
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: DejaVu Sans, Arial, sans-serif;
                    font-size: 9px;
                    color: #111;
                    margin: 15px 20px;
                }
                .header {
                    text-align: center;
                    margin-bottom: 16px;
                    border-bottom: 2px solid #003580;
                    padding-bottom: 10px;
                }
                .header .logo-line {
                    font-size: 12px;
                    font-weight: bold;
                    color: #003580;
                }
                .header .dept { font-size: 10px; color: #333; }
                .header .titre {
                    font-size: 12px;
                    font-weight: bold;
                    margin: 6px 0 2px;
                }
                .header .session { font-size: 9px; color: #555; }
                .header .annee  { font-size: 9px; color: #555; }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 8px;
                }
                thead th {
                    background: #003580;
                    color: #fff;
                    font-size: 8px;
                    font-weight: bold;
                    padding: 5px 4px;
                    border: 1px solid #002060;
                    text-align: center;
                }
                td {
                    padding: 4px 5px;
                    border: 1px solid #ccc;
                    vertical-align: middle;
                }
                .center { text-align: center; }
                .footer {
                    text-align: right;
                    font-size: 7px;
                    color: #999;
                    margin-top: 10px;
                    border-top: 1px solid #ddd;
                    padding-top: 3px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo-line">Ecole Nationale des Sciences Appliquées - Al Hoceima</div>
                <div class="dept">Département Mathématiques et Informatique</div>
                <div class="titre">Planning des soutenances des Projets de Fin d'Etude</div>
                <div class="session">(Première Session)</div>
                <div class="annee">Année Universitaire 2025/2026</div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width:30px;">ID</th>
                        <th>Encadrant</th>
                        <th>Membre de jury 1</th>
                        <th>Membre de jury 2</th>
                        <th style="width:65px;">Date</th>
                        <th style="width:35px;">Heure</th>
                        <th style="width:50px;">Salle</th>
                        <th>Nom d'étudiant</th>
                        <th>Prénom d'étudiant</th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>

            <div class="footer">Généré le {$date} — PFE Scheduler ENSA Al-Hoceima</div>
        </body>
        </html>
        HTML;
    }

    // ─────────────────────────────────────────────────────────────────
    //  RENDERER DOMPDF
    // ─────────────────────────────────────────────────────────────────
    private function renderToPdf(string $html, string $filename, string $orientation = 'landscape'): string
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        $path = $this->outputDir . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($path, $dompdf->output());

        return $path;
    }
}