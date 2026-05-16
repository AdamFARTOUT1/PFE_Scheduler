@extends('layout.app')

@section('titre', 'Importation des Données')

@section('contenu')

<div class="container">

    <h1 class="titre-page" style="text-align: center; border-bottom: none;">Importation des Données</h1>
    <p style="text-align: center; color: #7f8c8d; margin-bottom: 25px;">Importer les étudiants, professeurs et salles depuis un fichier Excel</p>

    <!-- Statistiques -->
    <div class="stats">
        <div class="stat-card">
            <h3 style="color: #12f32cff;">{{ $stats['total_etudiants'] }}</h3>
            <p>Étudiants</p>
        </div>
        <div class="stat-card">
            <h3 style="color: #12f32cff;">{{ $stats['total_professeurs'] }}</h3>
            <p>Professeurs</p>
        </div>
        @foreach ($stats['par_filiere'] as $fil)
        <div class="stat-card">
            <h3 style="color: #12f32cff;">{{ $fil->total }}</h3>
            <p>{{ $fil->filiere }}</p>
        </div>
        @endforeach
    </div>

    <!-- Section d'importation -->
    <div style="background: #f0f7ff; border: 2px solid #000000ff; border-radius: 8px; padding: 25px; margin: 25px 0;">
        <h2 style="color: #000000ff; font-size: 18px; margin-top: 0; margin-bottom: 15px;">Importer les Données</h2>

        <div style="background-color: #ffc8c8ff; padding: 15px; margin: 15px 0; border-radius: 4px; font-size: 14px;">
            <strong style="color: #ed0000ff;">Format du fichier Excel requis :</strong><br>
            Votre fichier doit contenir exactement 3 feuilles :
            <ul style="margin: 10px 0 0; padding-left: 20px;">
                <li><strong>Salles</strong> - Colonnes : (A) Nom, (B) Type (Salle/Amphi)</li>
                <li><strong>Professeurs</strong> - Colonnes : (B) Nom, (C) Prénom, (D) Spécialité</li>
                <li><strong>Étudiants</strong> - Colonnes : (A) Nom, (B) Prénom, (C) Filière (TDIA/ID), (D) Langue (fr/an)</li>
            </ul>
        </div>

        <form action="{{ route('import.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div style="margin: 15px 0;">
                <label for="file_unified" style="display: block; font-weight: bold; color: #333; margin-bottom: 8px;">
                    Sélectionner le fichier Excel unifié :
                </label>
                <input type="file" name="file_unified" id="file_unified" accept=".xlsx,.xls" required
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <button type="submit" class="btn-generer">
                Importer toutes les données
            </button>
        </form>
    </div>

</div>

@endsection
