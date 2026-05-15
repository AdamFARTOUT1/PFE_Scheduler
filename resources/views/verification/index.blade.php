@extends('layout.app')

@section('titre', 'Vérification des Contraintes')

@section('contenu')

<div class="container">

    <h1 class="titre-page" style="text-align: center; border-bottom: none;">Vérification des Contraintes</h1>
    <p style="text-align: center; color: #7f8c8d; margin-bottom: 25px;">Contrôle de la validité du planning généré</p>

    <!-- Statistiques de vérification -->
    <div class="stats">
        <div class="stat-card success">
            <h3>{{ $totalOk }}</h3>
            <p>Soutenances OK</p>
        </div>
        <div class="stat-card error">
            <h3>{{ count($erreurs) }}</h3>
            <p>Erreurs</p>
        </div>
        <div class="stat-card warning">
            <h3>{{ count($warnings) }}</h3>
            <p>Avertissements</p>
        </div>
        <div class="stat-card">
            <h3>{{ $plannings->count() }}</h3>
            <p>Total Soutenances</p>
        </div>
    </div>

    <!-- Erreurs critiques -->
    @if(count($erreurs) > 0)
    <h2 style="font-size: 18px; color: #333; border-bottom: 2px solid #e74c3c; padding-bottom: 10px; margin-top: 30px;">
        ❌ Erreurs Critiques ({{ count($erreurs) }})
    </h2>
    <div>
        @foreach($erreurs as $erreur)
            <div class="issue-item">
                <strong>{{ $erreur['titre'] }}</strong>
                <p>{{ $erreur['detail'] }}</p>
            </div>
        @endforeach
    </div>
    @else
    <h2 style="font-size: 18px; color: #333; border-bottom: 2px solid #27ae60; padding-bottom: 10px; margin-top: 30px;">
        ✓ Erreurs Critiques
    </h2>
    <div class="alert alert-success">
        <strong>Excellent !</strong> Aucune erreur critique détectée.
    </div>
    @endif

    <!-- Avertissements -->
    @if(count($warnings) > 0)
    <h2 style="font-size: 18px; color: #333; border-bottom: 2px solid #f39c12; padding-bottom: 10px; margin-top: 30px;">
        ⚠️ Avertissements ({{ count($warnings) }})
    </h2>
    <div>
        @foreach($warnings as $warning)
            <div class="issue-item warning">
                <strong>{{ $warning['titre'] }}</strong>
                <p>{{ $warning['detail'] }}</p>
            </div>
        @endforeach
    </div>
    @else
    <h2 style="font-size: 18px; color: #333; border-bottom: 2px solid #27ae60; padding-bottom: 10px; margin-top: 30px;">
        ✓ Avertissements
    </h2>
    <div class="alert alert-success">
        <strong>Excellent !</strong> Aucun avertissement.
    </div>
    @endif

    <!-- Résumé -->
    <h2 style="font-size: 18px; color: #333; border-bottom: 2px solid #2d6abf; padding-bottom: 10px; margin-top: 30px;">
        Résumé
    </h2>
    @if(count($erreurs) === 0 && count($warnings) === 0)
        <div class="alert alert-success">
            <strong>✓ Tous les contrôles sont validés !</strong> Le planning respecte toutes les contraintes.
        </div>
    @elseif(count($erreurs) > 0)
        <div class="alert alert-error">
            <strong>✗ Attention !</strong> Il y a {{ count($erreurs) }} erreur(s) critique(s) à corriger avant de pouvoir générer les exports.
        </div>
    @else
        <div class="alert alert-warning">
            <strong>⚠️ Attention !</strong> Il y a {{ count($warnings) }} avertissement(s) à vérifier.
        </div>
    @endif

</div>

@endsection