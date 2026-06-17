<?php

namespace App\Utils;

use App\Models\Etudiant;

/**
 * Helper centralisé pour les couleurs dynamiques de filières.
 *
 * Toutes les filières lues depuis l'Excel/la base sont automatiquement
 * associées à une couleur unique, sans aucun hardcoding de "ID", "TDIA", etc.
 *
 * Utilisé par :
 *  - AppServiceProvider (injection CSS dans les vues)
 *  - WordExporterService (tableaux DOCX)
 *  - PdfExporterService (tableaux PDF)
 */
class FiliereColorHelper
{
    /**
     * Palette de couleurs assignées séquentiellement aux filières.
     * Peut être agrandie si nécessaire.
     */
    const PALETTE = [
        '#3498db', // bleu
        '#27ae60', // vert
        '#8e44ad', // violet
        '#e67e22', // orange
        '#e74c3c', // rouge
        '#16a085', // turquoise
        '#2980b9', // bleu foncé
        '#f39c12', // jaune
        '#d35400', // orange foncé
        '#1abc9c', // vert clair
        '#9b59b6', // violet clair
        '#2ecc71', // vert vif
        '#f4c2c2', // rose clair
        '#d1c4e9', // lavande
        '#b2dfdb', // menthe
        '#ffe082', // jaune clair
    ];

    /**
     * Retourne un tableau [filiere_lowercase => couleur_hex]
     * pour toutes les filières distinctes présentes en base.
     *
     * @return array<string, string>
     */
    public static function getColors(): array
    {
        $filieres = Etudiant::select('filiere')
            ->distinct()
            ->orderBy('filiere')
            ->pluck('filiere')
            ->filter()
            ->values()
            ->all();

        $colors = [];
        $palette = self::PALETTE;
        foreach ($filieres as $idx => $filiere) {
            $key = strtolower(trim($filiere));
            $colors[$key] = $palette[$idx % count($palette)];
        }

        return $colors;
    }

    /**
     * Retourne la couleur d'une filière spécifique.
     * Si elle n'est pas trouvée, retourne un gris par défaut.
     *
     * @param string $filiere
     * @param array|null $colors  Tableau pré-calculé (optimisation pour éviter des requêtes répétées)
     * @return string
     */
    public static function getColor(string $filiere, ?array $colors = null): string
    {
        if ($colors === null) {
            $colors = self::getColors();
        }
        $key = strtolower(trim($filiere));
        return $colors[$key] ?? '#95a5a6'; // gris par défaut
    }

    /**
     * Retourne la liste des filières distinctes (valeurs originales, non-lowercase).
     *
     * @return array<string>
     */
    public static function getList(): array
    {
        return Etudiant::select('filiere')
            ->distinct()
            ->orderBy('filiere')
            ->pluck('filiere')
            ->filter()
            ->values()
            ->all();
    }
}
