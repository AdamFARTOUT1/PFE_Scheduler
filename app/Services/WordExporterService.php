<?php

namespace App\Services;

use App\Models\Planning;
use App\Models\Professeur;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Table;
use PhpOffice\PhpWord\SimpleType\Jc;
use ZipArchive;

class WordExporterService
{
    private string $outputDir;
    private string $pvDir;

    // ── Couleurs identiques aux PDFs ──
    const BLEU_ENSA = '003580';
    const BLEU_MID = '1e3a8a';
    const GRIS_HEADER = 'D0D8E8';
    const BLEU_LIGHT = 'EEF2FF';
    const BLANC = 'FFFFFF';
    const NOIR = '111111';

    public function __construct()
    {
        $this->outputDir = storage_path('app/outputs');
        $this->pvDir = $this->outputDir . DIRECTORY_SEPARATOR . 'PV';

        foreach ([$this->outputDir, $this->pvDir] as $dir) {
            if (!is_dir($dir))
                mkdir($dir, 0775, true);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  AFFECTATION — DOCX
    //  Format : Encadrant | Etudiant 1 | Etudiant 2 | ... (horizontal)
    //  Groupé par filière : ID puis TDIA
    // ═══════════════════════════════════════════════════════════════
    public function generateAffectation(): string
    {
        $phpWord = $this->baseDoc('Affectation des Encadrants PFE');
        $professeurs = Professeur::with('etudiants')->orderBy('nom')->get()
            ->filter(fn($p) => $p->etudiants->count() > 0);

        $maxEtudiants = max($professeurs->max(fn($p) => $p->etudiants->count()), 3);

        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'paperSize' => 'A4',
            'marginTop' => 700,
            'marginBottom' => 700,
            'marginLeft' => 700,
            'marginRight' => 700,
        ]);

        $this->header($section, 'Affectation des encadrants de Projet de Fin d\'Etude');
        $this->subtitle($section, 'Année Universitaire 2024/2025');
        $section->addTextBreak(1);

        // Largeur des colonnes
        $wEncadrant = 2800;
        $wEtudiant = (int) ((12000 - $wEncadrant) / $maxEtudiants);

        foreach (['ID', 'TDIA'] as $filiere) {
            $profsFiliere = $professeurs->filter(
                fn($p) => $p->etudiants->where('filiere', $filiere)->count() > 0
            );
            if ($profsFiliere->isEmpty())
                continue;

            // Titre section filière
            $section->addText(
                "Filière {$filiere}",
                ['bold' => true, 'size' => 11, 'color' => self::BLANC],
                ['alignment' => Jc::CENTER]
            );
            // Hack: titre en cellule colorée via tableau 1x1
            $this->addFiliereTitle($phpWord, $section, "Filière {$filiere}", $wEncadrant + $wEtudiant * $maxEtudiants);

            $styleName = 'Aff_' . $filiere;
            $phpWord->addTableStyle($styleName, [
                'borderSize' => 4,
                'borderColor' => 'AAAAAA',
                'cellMargin' => 50,
            ]);
            $table = $section->addTable($styleName);

            // ── En-tête du tableau ──
            $table->addRow(450);
            $table->addCell($wEncadrant, ['bgColor' => self::BLEU_ENSA])
                ->addText('Encadrant', ['bold' => true, 'size' => 9, 'color' => self::BLANC], ['alignment' => Jc::CENTER]);

            for ($i = 1; $i <= $maxEtudiants; $i++) {
                $table->addCell($wEtudiant, ['bgColor' => self::GRIS_HEADER])
                    ->addText("Etudiant {$i}", ['bold' => true, 'size' => 9], ['alignment' => Jc::CENTER]);
            }

            // ── Lignes de données ──
            $rowIdx = 0;
            foreach ($profsFiliere as $prof) {
                $etudiants = $prof->etudiants->where('filiere', $filiere)->values();
                $bg = ($rowIdx % 2 === 0) ? self::BLEU_LIGHT : self::BLANC;

                $table->addRow(380);
                $table->addCell($wEncadrant, ['bgColor' => self::BLEU_LIGHT])
                    ->addText($prof->nom . ' ' . $prof->prenom, ['bold' => true, 'size' => 9]);

                for ($i = 0; $i < $maxEtudiants; $i++) {
                    $txt = isset($etudiants[$i])
                        ? strtoupper($etudiants[$i]->nom) . ' ' . strtoupper($etudiants[$i]->prenom)
                        : '—';
                    $table->addCell($wEtudiant, ['bgColor' => $bg])
                        ->addText($txt, ['size' => 9], ['alignment' => Jc::CENTER]);
                }
                $rowIdx++;
            }

            $section->addTextBreak(2);
        }

        $this->footer($section);

        $path = $this->outputDir . DIRECTORY_SEPARATOR . 'affectation.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($path);
        return $path;
    }

    // ═══════════════════════════════════════════════════════════════
    //  PLANNING — DOCX
    //  Format : ID | Encadrant | Jury 1 | Jury 2 | Date | Heure | Salle | Nom | Prénom
    // ═══════════════════════════════════════════════════════════════
    public function generatePlanning(): string
    {
        $phpWord = $this->baseDoc('Planning des Soutenances PFE');
        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'paperSize' => 'A4',
            'marginTop' => 700,
            'marginBottom' => 700,
            'marginLeft' => 700,
            'marginRight' => 700,
        ]);

        $plannings = Planning::with(['etudiant', 'encadrant', 'jury2', 'jury3', 'jury4', 'creneau', 'salle'])
            ->join('creneaux', 'plannings.creneau_id', '=', 'creneaux.id')
            ->orderBy('creneaux.date_pfe')
            ->orderBy('creneaux.heure_debut')
            ->select('plannings.*')
            ->get();

        $this->header($section, 'Planning des soutenances des Projets de Fin d\'Etude');
        $this->subtitle($section, '(Session Normale) — Année Universitaire 2025/2026');
        $section->addTextBreak(1);

        $phpWord->addTableStyle('PlanningTable', [
            'borderSize' => 4,
            'borderColor' => 'AAAAAA',
            'cellMargin' => 50,
        ]);
        $table = $section->addTable('PlanningTable');

        // ── En-tête ──
        $headers = ['ID', 'Encadrant', 'Membre de jury 1', 'Membre de jury 2', 'Membre de jury 3', 'Date', 'Heure', 'Salle', 'Nom d\'étudiant', 'Prénom d\'étudiant'];
        $widths = [400, 1900, 1900, 1900, 900, 500, 700, 1700, 1700];

        $table->addRow(450);
        foreach ($headers as $i => $h) {
            $table->addCell($widths[$i], ['bgColor' => self::BLEU_ENSA])
                ->addText($h, ['bold' => true, 'size' => 8, 'color' => self::BLANC], ['alignment' => Jc::CENTER]);
        }

        // ── Données ──
        $id = 1;
        foreach ($plannings as $p) {
            $heure = intval(substr($p->creneau->heure_debut, 0, 2)) . 'h';
            $date = \Carbon\Carbon::parse($p->creneau->date_pfe)->format('d/m/Y');
            $bg = ($id % 2 === 0) ? self::BLEU_LIGHT : self::BLANC;
            $ts = ['size' => 8];

            $table->addRow(360);
            $table->addCell($widths[0], ['bgColor' => $bg])->addText((string) $id, $ts, ['alignment' => Jc::CENTER]);
            $table->addCell($widths[1], ['bgColor' => $bg])->addText($p->encadrant->nom . ' ' . $p->encadrant->prenom, $ts);
            $table->addCell($widths[2], ['bgColor' => $bg])->addText($p->jury2->nom . ' ' . $p->jury2->prenom, $ts);
            $table->addCell($widths[3], ['bgColor' => $bg])->addText($p->jury3->nom . ' ' . $p->jury3->prenom, $ts);
            $table->addCell($widths[4], ['bgColor' => $bg])->addText(($p->jury4 ? $p->jury4->nom . ' ' . $p->jury4->prenom : ''), $ts);
            $table->addCell($widths[5], ['bgColor' => $bg])->addText($date, $ts, ['alignment' => Jc::CENTER]);
            $table->addCell($widths[6], ['bgColor' => $bg])->addText($heure, $ts, ['alignment' => Jc::CENTER]);
            $table->addCell($widths[7], ['bgColor' => $bg])->addText($p->salle->nom, $ts, ['alignment' => Jc::CENTER]);
            $table->addCell($widths[8], ['bgColor' => $bg])->addText(strtoupper($p->etudiant->nom), $ts);
            $table->addCell($widths[9], ['bgColor' => $bg])->addText(strtoupper($p->etudiant->prenom), $ts);

            $id++;
        }

        $this->footer($section);

        $path = $this->outputDir . DIRECTORY_SEPARATOR . 'planning.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($path);
        return $path;
    }

    // ═══════════════════════════════════════════════════════════════
    //  PVs D'ÉVALUATION — 1 DOCX par étudiant + ZIP
    // ═══════════════════════════════════════════════════════════════
    public function generatePVsZip(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $plannings = Planning::with(['etudiant', 'encadrant', 'jury2', 'jury3', 'jury4', 'creneau', 'salle'])->get();

        $this->emptyDirectory($this->pvDir);

        foreach ($plannings->groupBy('encadrant_id') as $items) {
            $encadrant = $items->first()->encadrant;
            $encDirName = $this->slug($encadrant->nom . '_' . $encadrant->prenom);
            $encDir = $this->pvDir . DIRECTORY_SEPARATOR . $encDirName;

            if (!is_dir($encDir))
                mkdir($encDir, 0775, true);

            foreach ($items as $planning) {
                $this->generateSinglePV($planning, $encDir);
            }
        }

        $zipPath = $this->outputDir . DIRECTORY_SEPARATOR . 'PVs_Evaluations.zip';
        $this->zipDirectory($this->pvDir, $zipPath);

        return response()->download($zipPath, 'PVs_Evaluations_' . now()->format('Ymd') . '.zip', [
            'Content-Type' => 'application/zip',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    //  PV INDIVIDUEL — structure calquée sur la fiche exemple
    // ─────────────────────────────────────────────────────────────────
    private function generateSinglePV(Planning $planning, string $dir): void
    {
        $phpWord = $this->baseDoc('Fiche Evaluation PFE');
        $etudiant = $planning->etudiant;
        $enc = $planning->encadrant;
        $creneau = $planning->creneau;
        $salle = $planning->salle;

        $date = \Carbon\Carbon::parse($creneau->date_pfe)->format('d/m/Y');
        $heure = intval(substr($creneau->heure_debut, 0, 2)) . 'h';

        $section = $phpWord->addSection(['paperSize' => 'A4', 'marginTop' => 800, 'marginBottom' => 800, 'marginLeft' => 900, 'marginRight' => 900]);

        // ── 1. En-tête ENSA ─────────────────────────────────────────
        $this->header($section, 'Ecole Nationale des Sciences Appliquées - Al Hoceima');
        $section->addText(
            'Département Mathématiques et Informatique',
            ['size' => 10, 'color' => '333333'],
            ['alignment' => Jc::CENTER]
        );
        $section->addText(
            'Fiche d\'Évaluation — Projet de Fin d\'Etude',
            ['bold' => true, 'size' => 13, 'color' => self::BLEU_ENSA],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 80]
        );
        $section->addText(
            'Année Universitaire 2025/2026',
            ['size' => 10, 'italic' => true, 'color' => '555555'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 160]
        );

        // ── 2. Bloc infos étudiant ───────────────────────────────────
        $phpWord->addTableStyle('InfoPV', [
            'borderSize' => 6,
            'borderColor' => 'AAAAAA',
            'cellMargin' => 70,
        ]);
        $tInfo = $section->addTable('InfoPV');

        $infoRows = [
            ['Nom & Prénom', strtoupper($etudiant->nom) . ' ' . strtoupper($etudiant->prenom)],
            ['Filière', $etudiant->filiere],
            ['Date de soutenance', $date],
            ['Heure', $heure],
            ['Salle', $salle->nom],
            ['Encadrant (Jury 1)', $enc->nom . ' ' . $enc->prenom],
            ['Membre de jury 2', $planning->jury2->nom . ' ' . $planning->jury2->prenom],
            ['Membre de jury 3', $planning->jury3->nom . ' ' . $planning->jury3->prenom],
            ['Membre de jury 4', $planning->jury4 ? $planning->jury4->nom . ' ' . $planning->jury4->prenom : '—'],
        ];

        foreach ($infoRows as $i => [$label, $val]) {
            $bg = ($i % 2 === 0) ? self::BLEU_LIGHT : self::BLANC;
            $tInfo->addRow(380);
            $tInfo->addCell(3500, ['bgColor' => self::BLEU_ENSA])
                ->addText($label, ['bold' => true, 'size' => 9, 'color' => self::BLANC]);
            $tInfo->addCell(6000, ['bgColor' => $bg])
                ->addText($val, ['size' => 10]);
        }

        $section->addTextBreak(2);

        // ── 3. Grille de notation ────────────────────────────────────
        $section->addText(
            'Grille de Notation',
            ['bold' => true, 'size' => 12, 'color' => self::BLEU_ENSA],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 80]
        );

        $phpWord->addTableStyle('NotesPV', [
            'borderSize' => 6,
            'borderColor' => 'AAAAAA',
            'cellMargin' => 70,
        ]);
        $tNotes = $section->addTable('NotesPV');

        // En-tête grille
        $tNotes->addRow(420);
        foreach (['Critère d\'évaluation', 'Coefficient', 'Note  /20', 'Appréciation'] as $h) {
            $tNotes->addCell(2375, ['bgColor' => self::BLEU_ENSA])
                ->addText($h, ['bold' => true, 'size' => 9, 'color' => self::BLANC], ['alignment' => Jc::CENTER]);
        }

        // Critères vierges (à remplir par le jury)
        $criteres = [
            ['Contenu du rapport', '0,50'],
            ['Mémoire / rapport écrit', '0,20'],
            ['Soutenance orale', '0,30'],
        ];

        foreach ($criteres as $i => [$crit, $coef]) {
            $bg = ($i % 2 === 0) ? self::BLEU_LIGHT : self::BLANC;
            $tNotes->addRow(500);
            $tNotes->addCell(2375, ['bgColor' => $bg])->addText($crit, ['size' => 9]);
            $tNotes->addCell(2375, ['bgColor' => $bg])->addText($coef, ['size' => 9], ['alignment' => Jc::CENTER]);
            $tNotes->addCell(2375, ['bgColor' => $bg])->addText('', ['size' => 9]); // à remplir
            $tNotes->addCell(2375, ['bgColor' => $bg])->addText('', ['size' => 9]); // à remplir
        }

        // Ligne moyenne finale
        $tNotes->addRow(480);
        $tNotes->addCell(2375, ['bgColor' => self::BLEU_MID])
            ->addText('MOYENNE FINALE', ['bold' => true, 'size' => 9, 'color' => self::BLANC]);
        $tNotes->addCell(2375, ['bgColor' => self::BLEU_MID])
            ->addText('C×0,5 + M×0,2 + S×0,3', ['size' => 8, 'color' => self::BLANC], ['alignment' => Jc::CENTER]);
        $tNotes->addCell(2375, ['bgColor' => self::BLEU_MID])
            ->addText('', ['size' => 9, 'color' => self::BLANC]);
        $tNotes->addCell(2375, ['bgColor' => self::BLEU_MID])
            ->addText('', ['size' => 9]);

        $section->addTextBreak(3);

        // ── 4. Zone signatures ───────────────────────────────────────
        $phpWord->addTableStyle('SigPV', ['cellMargin' => 80]);
        $tSig = $section->addTable('SigPV');
        $tSig->addRow(1200);

        $membres = [
            ['name' => $enc->nom . ' ' . $enc->prenom, 'role' => '(Encadrant)'],
            ['name' => $planning->jury2->nom . ' ' . $planning->jury2->prenom, 'role' => '(Jury 2)'],
            ['name' => $planning->jury3->nom . ' ' . $planning->jury3->prenom, 'role' => '(Jury 3)'],
        ];
        if ($planning->jury4) {
            $membres[] = ['name' => $planning->jury4->nom . ' ' . $planning->jury4->prenom, 'role' => '(Jury 4)'];
        }

        foreach ($membres as $m) {
            $cell = $tSig->addCell(3167, ['borderSize' => 0]);
            $cell->addText($m['name'], ['bold' => true, 'size' => 9], ['alignment' => Jc::CENTER]);
            $cell->addText($m['role'], ['size' => 8, 'italic' => true], ['alignment' => Jc::CENTER]);
            $cell->addTextBreak(1);
            $cell->addText('Signature :', ['size' => 8, 'color' => '888888'], ['alignment' => Jc::CENTER]);
            $cell->addTextBreak(2);
        }

        $this->footer($section);

        // Sauvegarde
        $filename = $this->slug($etudiant->nom . '_' . $etudiant->prenom) . '.docx';
        IOFactory::createWriter($phpWord, 'Word2007')
            ->save($dir . DIRECTORY_SEPARATOR . $filename);
    }

    // ═══════════════════════════════════════════════════════════════
    //  HELPERS PRIVÉS
    // ═══════════════════════════════════════════════════════════════

    private function baseDoc(string $title): PhpWord
    {
        $phpWord = new PhpWord();
        $phpWord->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language('fr-FR'));
        $phpWord->getDocInfo()->setTitle($title);
        $phpWord->getDocInfo()->setCreator('PFE Scheduler — ENSA Al-Hoceima');
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(10);
        return $phpWord;
    }

    private function header(\PhpOffice\PhpWord\Element\Section $s, string $title): void
    {
        $s->addText(
            $title,
            ['bold' => true, 'size' => 13, 'color' => self::BLEU_ENSA],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 40]
        );
    }

    private function subtitle(\PhpOffice\PhpWord\Element\Section $s, string $sub): void
    {
        $s->addText(
            $sub,
            ['size' => 10, 'italic' => true, 'color' => '555555'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 100]
        );
    }

    private function footer(\PhpOffice\PhpWord\Element\Section $s): void
    {
        $footer = $s->addFooter();
        $footer->addText(
            'PFE Scheduler — ENSA Al-Hoceima • Généré le ' . now()->format('d/m/Y'),
            ['size' => 7, 'color' => '999999'],
            ['alignment' => Jc::RIGHT]
        );
    }

    /** Titre coloré pour section filière */
    private function addFiliereTitle(PhpWord $phpWord, $section, string $txt, int $totalWidth): void
    {
        $phpWord->addTableStyle('FiliereTitle', ['cellMargin' => 60]);
        $t = $section->addTable('FiliereTitle');
        $t->addRow(380);
        $t->addCell($totalWidth, ['bgColor' => self::BLEU_ENSA])
            ->addText(
                $txt,
                ['bold' => true, 'size' => 10, 'color' => self::BLANC],
                ['alignment' => Jc::CENTER]
            );
    }

    private function slug(string $name): string
    {
        $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name) ?: $name;
        return preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);
    }

    private function emptyDirectory(string $dir): void
    {
        if (!is_dir($dir))
            return;
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item) : unlink($item);
        }
    }

    private function zipDirectory(string $sourceDir, string $zipPath): void
    {
        if (file_exists($zipPath))
            unlink($zipPath);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            throw new \RuntimeException("Impossible de créer le ZIP.");
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $file) {
            if (!$file->isFile())
                continue;
            $relative = 'PV' . DIRECTORY_SEPARATOR . substr($file->getRealPath(), strlen($sourceDir) + 1);
            $zip->addFile($file->getRealPath(), $relative);
        }
        $zip->close();
    }
}