@extends('layout.app')

@section('titre', 'Importation des Données')

@section('contenu')

    <div class="container">

        <h1 class="titre-page" style="text-align: center; border-bottom: none;">Importation des Données</h1>
        <p style="text-align: center; color: #7f8c8d; margin-bottom: 25px;">Importer les étudiants, professeurs et salles
            depuis un fichier Excel</p>

        <!-- Statistiques -->
        <div class="stats">
            <div class="stat-card">
                <h3 style="color: #2d6abf;">{{ $stats['total_etudiants'] }}</h3>
                <p>Étudiants</p>
            </div>
            <div class="stat-card">
                <h3 style="color: #2d6abf;">{{ $stats['total_professeurs'] }}</h3>
                <p>Professeurs</p>
            </div>
            @foreach ($stats['par_filiere'] as $fil)
                <div class="stat-card">
                    <h3 style="color: #2d6abf;">{{ $fil->total }}</h3>
                    <p>{{ $fil->filiere }}</p>
                </div>
            @endforeach
        </div>

        <!-- Section d'importation -->
        <div style="background: #f0f7ff; border: 2px solid #000000ff; border-radius: 8px; padding: 25px; margin: 25px 0; text-align: center;">
            <h2 style="color: #000000ff; font-size: 18px; margin-top: 0; margin-bottom: 15px;">Importer les Données</h2>

            
            <form action="{{ route('import.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div style="margin: 15px 0; display: flex; flex-direction: column; align-items: center;">
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

        <!-- Section de réinitialisation -->
        <div style="text-align: center; margin-top: 40px; padding: 20px; border-top: 1px solid #eee;">
            <h3 style="font-size: 16px; color: #7f8c8d; margin-bottom: 15px;">Zone de Danger</h3>
            <form action="{{ route('import.reset') }}" method="POST"
                onsubmit="return confirm('ATTENTION ! Cette action va supprimer définitivement TOUTES les données (étudiants, professeurs, salles et plannings). Voulez-vous continuer ?');">
                @csrf
                <button type="submit" class="btn-reset"
                    style="background-color: #e74c3c; color: white; border: none; padding: 10px 25px; border-radius: 6px; cursor: pointer; font-weight: bold; transition: background 0.3s;">
                    Réinitialiser la Base de Données
                </button>
            </form>
        </div>

    </div>

@endsection