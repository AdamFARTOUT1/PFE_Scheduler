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

    const BLEU_ENSA = '003580';
    const BLEU_MID = '1e3a8a';
    const GRIS_HEADER = 'D0D8E8';
    const BLEU_LIGHT = 'EEF2FF';
    const BLANC = 'FFFFFF';

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
    // ═══════════════════════════════════════════════════════════════
    public function generateAffectation(): string
    {
        $phpWord = $this->baseDoc('Affectation des Encadrants PFE');
        $plannings = Planning::with(['etudiant', 'encadrant'])->get();

        $professeurs = $plannings->groupBy('encadrant_id')->map(function ($items) {
            $prof = $items->first()->encadrant;
            if ($prof) {
                $prof->setRelation('etudiants', $items->map->etudiant);
            }
            return $prof;
        })->filter()->sortBy('nom')->values();

        $maxEtudiants = max($professeurs->max(fn($p) => $p->etudiants->count()), 3);

        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'paperSize' => 'A4',
            'marginTop' => 700,
            'marginBottom' => 700,
            'marginLeft' => 600,
            'marginRight' => 700,
        ]);

        $this->entete($section, "Affectation des encadrants de Projet de Fin d'Etude");
        $this->sousTitre($section, 'Année Universitaire 2025/2026');
        $section->addTextBreak(1);

        $wEnc = 2800;
        $wEtu = (int) ((12000 - $wEnc) / $maxEtudiants);

        // Légende des couleurs
        $section->addText('Légende :', ['bold' => true, 'size' => 10]);
        $tr = $section->addTextRun();
        $tr->addText('      ID', ['color' => '80CBC4', 'size' => 10], ['shading' => ['fill' => '80CBC4']]);
        $tr->addText('         ');
        $tr->addText('      TDIA', ['color' => 'FFAB91', 'size' => 10], ['shading' => ['fill' => 'FFAB91']]);
        $section->addTextBreak(1);

        $styleName = 'Aff_Globale';
        $phpWord->addTableStyle($styleName, ['borderSize' => 10, 'borderColor' => '999999', 'cellMargin' => 50]);
        $table = $section->addTable($styleName);

        $table->addRow(500);
        $table->addCell($wEnc, ['bgColor' => self::BLEU_ENSA])
            ->addText('Encadrant', ['bold' => true, 'size' => 9, 'color' => self::BLANC], ['alignment' => Jc::CENTER]);
        for ($i = 1; $i <= $maxEtudiants; $i++) {
            $table->addCell($wEtu, ['bgColor' => self::BLEU_ENSA])
                ->addText("Etudiant {$i}", ['bold' => true, 'size' => 9, 'color' => self::BLANC], ['alignment' => Jc::CENTER]);
        }

        $rowIdx = 0;
        foreach ($professeurs as $prof) {
            $etudiants = $prof->etudiants->values();
            $table->addRow(390);
            $table->addCell($wEnc, ['bgColor' => self::BLANC])
                ->addText($prof->nom . ' ' . $prof->prenom, ['bold' => true, 'size' => 9]);

            for ($i = 0; $i < $maxEtudiants; $i++) {
                if (isset($etudiants[$i])) {
                    $e = $etudiants[$i];
                    $txt = strtoupper($e->nom) . ' ' . strtoupper($e->prenom);
                    $etuBg = ($e->filiere === 'ID') ? '80CBC4' : 'FFAB91';
                } else {
                    $txt = '----';
                    $etuBg = self::BLANC;
                }

                $table->addCell($wEtu, ['bgColor' => $etuBg])
                    ->addText($txt, ['size' => 9], ['alignment' => Jc::CENTER]);
            }
            $rowIdx++;
        }
        $section->addTextBreak(2);

        $this->pied($section);
        $path = $this->outputDir . DIRECTORY_SEPARATOR . 'affectation.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($path);
        return $path;
    }

    // ═══════════════════════════════════════════════════════════════
    //  PLANNING — DOCX
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

        $this->entete($section, "Planning des soutenances des Projets de Fin d'Etude");
        $this->sousTitre($section, "(Première Session) — Année Universitaire 2024/2025");
        $section->addTextBreak(1);

        $phpWord->addTableStyle('PlanTable', ['borderSize' => 4, 'borderColor' => 'AAAAAA', 'cellMargin' => 50]);
        $table = $section->addTable('PlanTable');

        $headers = ['ID', 'Encadrant', 'Jury 1', 'Jury 2', 'Jury 3', 'Date', 'Heure', 'Salle', "Nom d'étudiant", "Prénom d'étudiant"];
        $widths = [365, 1600, 1600, 1600, 1600, 850, 450, 600, 1500, 1550];

        $table->addRow(450);
        foreach ($headers as $i => $h) {
            $table->addCell($widths[$i], ['bgColor' => self::BLEU_ENSA])
                ->addText($h, ['bold' => true, 'size' => 8, 'color' => self::BLANC], ['alignment' => Jc::CENTER]);
        }

        $profColors = [];
        $usedProfColors = [];
        $dayColors = [];
        $availableDayColors = ['FFCDD2', 'C8E6C9', 'BBDEFB', 'FFE082', 'E1BEE7', 'B2DFDB', 'D7CCC8', 'CFD8DC'];
        $dayColorIndex = 0;

        $availableProfColors = [
            'FFB3BA',
            'FFDFBA',
            'FFFFBA',
            'BAFFC9',
            'BAE1FF',
            'F4C2C2',
            'FADADD',
            'FDFD96',
            'C1E1C1',
            'AEC6CF',
            'FFCBA4',
            'FFD1DC',
            'C8E6C9',
            'D1C4E9',
            'B3E5FC',
            'FFCC80',
            'F8BBD0',
            'DCEDC8',
            'B2DFDB',
            'FFE082',
            'E1BEE7',
            'C5CAE9',
            'BCAAA4',
            'FFAB91',
            'FFE0B2',
            'F0F4C3',
            'E6EE9C',
            'CE93D8',
            '9FA8DA',
            '81D4FA',
            '80DEEA',
            '80CBC4',
            'A5D6A7',
            'C5E1A5',
            'FFF59D',
            'FFE082',
            'FFCC80',
            'FFAB91',
            'BCAAA4',
            'EEEEEE'
        ];
        $profColorIndex = 0;

        $getProfColor = function ($name) use (&$profColors, &$availableProfColors, &$profColorIndex) {
            $name = trim($name);
            if (empty($name))
                return self::BLANC;

            if (!isset($profColors[$name])) {
                $profColors[$name] = $availableProfColors[$profColorIndex % count($availableProfColors)];
                $profColorIndex++;
            }
            return $profColors[$name];
        };

        $getDayColor = function ($date) use (&$dayColors, &$availableDayColors, &$dayColorIndex) {
            if (empty($date))
                return self::BLANC;
            if (!isset($dayColors[$date])) {
                $dayColors[$date] = $availableDayColors[$dayColorIndex % count($availableDayColors)];
                $dayColorIndex++;
            }
            return $dayColors[$date];
        };

        $id = 1;
        foreach ($plannings as $p) {
            $heure = intval(substr($p->creneau->heure_debut, 0, 2)) . 'h';
            $date = \Carbon\Carbon::parse($p->creneau->date_pfe)->format('d/m/Y');

            $encadrantStr = $p->encadrant->nom . ' ' . $p->encadrant->prenom;
            $jury1Str = $p->jury2->nom . ' ' . $p->jury2->prenom;
            $jury2Str = $p->jury3->nom . ' ' . $p->jury3->prenom;
            $jury3Str = $p->jury4 ? ($p->jury4->nom . ' ' . $p->jury4->prenom) : '';

            $bgEnc = $getProfColor($encadrantStr);
            $bgJ1 = $getProfColor($jury1Str);
            $bgJ2 = $getProfColor($jury2Str);
            $bgJ3 = $jury3Str ? $getProfColor($jury3Str) : self::BLANC;
            $bgDate = $getDayColor($date);

            $bg = ($id % 2 === 0) ? self::BLEU_LIGHT : self::BLANC;
            $ts = ['size' => 8];

            $table->addRow(360);
            $table->addCell($widths[0], ['bgColor' => $bg])->addText((string) $id, $ts, ['alignment' => Jc::CENTER]);
            $table->addCell($widths[1], ['bgColor' => $bgEnc])->addText($encadrantStr, $ts);
            $table->addCell($widths[2], ['bgColor' => $bgJ1])->addText($jury1Str, $ts);
            $table->addCell($widths[3], ['bgColor' => $bgJ2])->addText($jury2Str, $ts);
            $table->addCell($widths[4], ['bgColor' => $bgJ3])->addText($jury3Str, $ts);
            $table->addCell($widths[5], ['bgColor' => $bgDate])->addText($date, $ts, ['alignment' => Jc::CENTER]);
            $table->addCell($widths[6], ['bgColor' => $bg])->addText($heure, $ts, ['alignment' => Jc::CENTER]);
            $table->addCell($widths[7], ['bgColor' => $bg])->addText($p->salle->nom, $ts, ['alignment' => Jc::CENTER]);
            $table->addCell($widths[8], ['bgColor' => $bg])->addText(strtoupper($p->etudiant->nom), $ts);
            $table->addCell($widths[9], ['bgColor' => $bg])->addText(strtoupper($p->etudiant->prenom), $ts);
            $id++;
        }

        $this->pied($section);
        $path = $this->outputDir . DIRECTORY_SEPARATOR . 'planning.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($path);
        return $path;
    }

    // ═══════════════════════════════════════════════════════════════
    //  PVs — ZIP
    // ═══════════════════════════════════════════════════════════════
    public function generatePVsZip(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $plannings = Planning::with(['etudiant', 'encadrant', 'jury2', 'jury3', 'jury4', 'creneau', 'salle'])
            ->join('creneaux', 'plannings.creneau_id', '=', 'creneaux.id')
            ->orderBy('creneaux.date_pfe')
            ->orderBy('creneaux.heure_debut')
            ->select('plannings.*')
            ->get();

        $this->viderDossier($this->pvDir);

        foreach ($plannings->groupBy('encadrant_id') as $items) {
            $encadrant = $items->first()->encadrant;
            $encDir = $this->pvDir . DIRECTORY_SEPARATOR . $this->slug($encadrant->nom . '_' . $encadrant->prenom);
            if (!is_dir($encDir))
                mkdir($encDir, 0775, true);

            foreach ($items as $planning) {
                $this->generateSinglePV($planning, $encDir);
            }
        }

        $zipPath = $this->outputDir . DIRECTORY_SEPARATOR . 'PVs_Evaluations.zip';
        $this->zipper($this->pvDir, $zipPath);

        return response()->download($zipPath, 'PVs_Evaluations_' . now()->format('Ymd') . '.zip', [
            'Content-Type' => 'application/zip',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  PV INDIVIDUEL — format exact du template
    // ═══════════════════════════════════════════════════════════════
    private function generateSinglePV(Planning $planning, string $dir): void
    {
        $phpWord = $this->baseDoc("Fiche d'évaluation PFE");
        $etudiant = $planning->etudiant;
        $enc = $planning->encadrant;
        $creneau = $planning->creneau;

        $date = \Carbon\Carbon::parse($creneau->date_pfe)->format('d/m/Y');

        $section = $phpWord->addSection([
            'paperSize' => 'A4',
            'marginTop' => 200,
            'marginBottom' => 400,
            'marginLeft' => 800,
            'marginRight' => 500,
        ]);

        $centre = ['alignment' => Jc::CENTER];
        $bu = ['bold' => true, 'underline' => 'single', 'size' => 11]; // bold + underline
        $buNormal = ['bold' => true, 'underline' => 'single', 'size' => 10];
        $normal = ['size' => 10];
        $small = ['size' => 9];
        $dots60 = str_repeat('.', 60);
        $dots50 = str_repeat('.', 50);
        $dots15 = str_repeat('.', 15);

        // ── 1. EN-TÊTE ──────────────────────────────────────────────
        // Ajouter le logo de l'uae
        $headerTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $headerTable->addRow();
        // Cellule Gauche : Logo UAE
        $headerTable->addCell(2100)->addImage(public_path('images/uae.png'), [
            'width' => 62,
            'height' => 62,
            'alignment' => Jc::CENTER
        ]);
        // Cellule Centre : Texte Université
        $centerCell = $headerTable->addCell(6100);
        $centerCell->addText('UNIVERSITE ABDELMALEK ESSAADI', $bu, [
            'alignment' => Jc::CENTER,
            'spaceBefore' => 180 // Ajoute environ 9 points d'espace au-dessus du texte
        ]);
        $centerCell->addText('Ecole Nationale des Sciences Appliquées d\'Al Hoceima - Maroc', $buNormal, $centre);
        // Cellule Droite : Logo ENSAH
        $headerTable->addCell(2100)->addImage(public_path('images/ensah.png'), [
            'width' => 62,
            'height' => 62,
            'alignment' => Jc::CENTER
        ]);
        $section->addText(
            'Département de Mathématiques et Informatique',
            $bu,
            $centre
        );
        $section->addText(
            "Fiche d'évaluation du Projet de Fin d'Etude",
            $bu,
            $centre
        );
        $section->addText(
            'Année Universitaire : 2025-2026',
            $buNormal,
            $centre
        );

        $section->addTextBreak(1);

        // ── 2. NOM - PRÉNOM ──────────────────────────────────────────
        $section->addText("Nom - Prénom de l'élève ingénieur :", $buNormal);

        $nomEtudiant = strtoupper($etudiant->nom) . ' ' . strtoupper($etudiant->prenom);
        $dotsNom = str_repeat('.', max(5, 52 - strlen($nomEtudiant)));
        $section->addListItem($nomEtudiant . ' ' . $dotsNom, 0, $normal);


        // ── 3. FILIÈRE ───────────────────────────────────────────────
        $idCheck = ($etudiant->filiere === 'ID') ? '☑' : '☐';
        $tdiaCheck = ($etudiant->filiere === 'TDIA') ? '☑' : '☐';

        $tr = $section->addTextRun();
        $tr->addText('Filière :', $buNormal);
        $tr->addText('         ' . $idCheck . ' Ingénierie Donnée  ', $normal);
        $tr->addText('   ' . $tdiaCheck . ' Transformation Digitale et Intelligence Artificielle', $normal);

        // ── 4. INTITULÉ DU RAPPORT ───────────────────────────────────
        $section->addText("Intitulé du rapport :", $buNormal);
        $section->addListItem($dots60, 0, $normal);

        // ── 5. ENCADRANT ─────────────────────────────────────────────
        $section->addText("L'encadrant(e) interne :", $buNormal);

        $encNom = 'Pr. ' . $enc->nom . ' ' . $enc->prenom;
        $dotsEnc = str_repeat('.', max(5, 52 - strlen($encNom)));
        $section->addListItem($encNom . ' ' . $dotsEnc, 0, $normal);

        // ── 6. MEMBRES DU JURY ───────────────────────────────────────
        $section->addText('Membres du jury :', $buNormal);

        $phpWord->addTableStyle('JuryPV', [
            'borderSize' => 4,
            'borderColor' => '000000',
            'cellMargin' => 60,
        ]);
        $juryTable = $section->addTable('JuryPV');

        $membres = array_filter([
            ['prof' => $planning->jury2, 'role' => 'Président'],
            ['prof' => $planning->jury3, 'role' => 'Rapporteur'],
            $planning->jury4 ? ['prof' => $planning->jury4, 'role' => 'Rapporteur'] : null,
        ]);

        foreach ($membres as $m) {
            $profNom = 'Pr. ' . $m['prof']->nom . ' ' . $m['prof']->prenom;
            $dotsProf = str_repeat('.', max(5, 48 - strlen($profNom)));

            $juryTable->addRow(420);
            $juryTable->addCell(7500)->addText(
                '  •  ' . $profNom . ' ',
                $normal
            );
            $juryTable->addCell(1800)->addText(
                $m['role'],
                $normal,
                ['alignment' => Jc::RIGHT]
            );
        }

        $section->addTextBreak(1);

        // ── 7. NOTE DU CONTENU ───────────────────────────────────────
        $trContenu = $section->addTextRun(['spaceAfter' => 40]);
        $trContenu->addText('Note du Contenu ', ['bold' => true, 'underline' => 'single', 'size' => 10]);
        $trContenu->addText("(En prenant en compte l'appréciation de l'entreprise)", ['italic' => true, 'size' => 9]);

        $section->addText('      C =', $normal);


        // ── 8. NOTE DU MÉMOIRE ───────────────────────────────────────
        $section->addText('Note du Mémoire', $buNormal);
        $section->addText('      M =', $normal);


        // ── 9. NOTE DE LA SOUTENANCE ─────────────────────────────────
        $section->addText('Note de la Soutenance', $buNormal);
        $section->addText('      S =', $normal);


        // ── 10. TABLEAU MOYENNE ───────────────────────────────────────
        $phpWord->addTableStyle('MoyennePV', [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
        ]);
        $moyTable = $section->addTable('MoyennePV');

        // Ligne header : MOYENNE (fond gris)
        $moyTable->addRow(300);
        $moyTable->addCell(9300, ['bgColor' => 'AAAAAA'])->addText(
            'MOYENNE',
            ['bold' => true, 'size' => 10],
            ['alignment' => Jc::CENTER]
        );

        // Ligne formule
        $moyTable->addRow(380);
        $moyTable->addCell(9300)->addText(
            'Moyenne   =   C * 0,5 + M * 0,2 + S * 0,3   =',
            ['bold' => true, 'size' => 10]
        );

        $section->addTextBreak(1);

        // ── 11. DATE + SIGNATURES ─────────────────────────────────────
        $section->addText('Le : ' . $date . '          ', $normal);

        $section->addText('Signature des membres du jury :', $normal);
        //les nom des membres du jury dans meme ligne avec underscore
        $nomMembres = '';
        foreach ($membres as $m) {
            $nomMembres .= 'Pr. ' . $m['prof']->nom . ' ' . $m['prof']->prenom . '          ';
        }
        $section->addTextBreak(1);
        $section->addText($nomMembres, $normal);
        $section->addTextBreak(1);

        // Sauvegarde
        $filename = $this->slug($etudiant->nom . '_' . $etudiant->prenom) . '.docx';
        IOFactory::createWriter($phpWord, 'Word2007')
            ->save($dir . DIRECTORY_SEPARATOR . $filename);
    }

    // ═══════════════════════════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════════════════════════

    private function baseDoc(string $titre): PhpWord
    {
        $phpWord = new PhpWord();
        $phpWord->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language('fr-FR'));
        $phpWord->getDocInfo()->setTitle($titre);
        $phpWord->getDocInfo()->setCreator('PFE Scheduler - ENSA Al-Hoceima');
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);
        return $phpWord;
    }

    private function entete(\PhpOffice\PhpWord\Element\Section $s, string $titre): void
    {
        $s->addText(
            $titre,
            ['bold' => true, 'size' => 13, 'color' => self::BLEU_ENSA],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 40]
        );
    }

    private function sousTitre(\PhpOffice\PhpWord\Element\Section $s, string $sub): void
    {
        $s->addText(
            $sub,
            ['size' => 10, 'italic' => true, 'color' => '555555'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 100]
        );
    }

    private function pied(\PhpOffice\PhpWord\Element\Section $s): void
    {
        $footer = $s->addFooter();
        $footer->addText(
            'Généré le ' . now()->format('d/m/Y'),
            ['size' => 7, 'color' => '999999'],
            ['alignment' => Jc::RIGHT]
        );
    }

    private function titreFiliereTable(PhpWord $phpWord, $section, string $txt, int $largeur): void
    {
        // Nom de style unique par titre pour éviter les doublons PhpWord
        $styleName = 'FilTitre_' . md5($txt);
        $phpWord->addTableStyle($styleName, ['cellMargin' => 60]);
        $t = $section->addTable($styleName);
        $t->addRow(380);
        $t->addCell($largeur, ['bgColor' => self::BLEU_ENSA])
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

    private function viderDossier(string $dir): void
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

    private function zipper(string $source, string $zipPath): void
    {
        if (file_exists($zipPath))
            unlink($zipPath);
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            throw new \RuntimeException("Impossible de créer le ZIP.");
        }
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $file) {
            if (!$file->isFile())
                continue;
            $relative = 'PV' . DIRECTORY_SEPARATOR . substr($file->getRealPath(), strlen($source) + 1);
            $zip->addFile($file->getRealPath(), $relative);
        }
        $zip->close();
    }
}