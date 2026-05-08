@extends('layout.app')

@section('titre', 'Vérification des contraintes')

@section('contenu')
<div class="container-fluid" style="padding: 20px 30px;">

    <h2 class="titre-page">✅ Vérification des contraintes</h2>

    {{-- Résumé --}}
    <div class="row" style="margin-bottom: 25px;">
        <div class="col-md-4">
            <div class="carte-stat" style="border-left-color: #e74c3c;">
                <div class="nombre" style="color: #e74c3c;">{{ count($erreurs) }}</div>
                <div class="label">Erreurs critiques</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="carte-stat" style="border-left-color: #f39c12;">
                <div class="nombre" style="color: #f39c12;">{{ count($warnings) }}</div>
                <div class="label">Avertissements</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="carte-stat">
                <div class="nombre">{{ $plannings->count() }}</div>
                <div class="label">Soutenances vérifiées</div>
            </div>
        </div>
    </div>

    {{-- Message si tout est OK --}}
    @if(count($erreurs) == 0 && count($warnings) == 0)
    <div class="anomalie ok">
        <div class="anomalie-titre">✅ Aucun problème détecté</div>
        <div class="anomalie-detail">
            Toutes les contraintes sont respectées pour les {{ $plannings->count() }} soutenances.
        </div>
    </div>
    @endif

    {{-- Erreurs critiques --}}
    @if(count($erreurs) > 0)
    <h4 style="color: #e74c3c; margin-bottom: 15px;">❌ Erreurs critiques ({{ count($erreurs) }})</h4>
    @foreach($erreurs as $erreur)
    <div class="anomalie erreur">
        <div class="anomalie-titre">❌ {{ $erreur['titre'] }}</div>
        <div class="anomalie-detail">{{ $erreur['detail'] }}</div>
    </div>
    @endforeach
    @endif

    {{-- Avertissements --}}
    @if(count($warnings) > 0)
    <h4 style="color: #f39c12; margin-bottom: 15px; margin-top: 25px;"> Avertissements ({{ count($warnings) }})</h4>
    @foreach($warnings as $warning)
    <div class="anomalie warning">
        <div class="anomalie-titre"> {{ $warning['titre'] }}</div>
        <div class="anomalie-detail">{{ $warning['detail'] }}</div>
    </div>
    @endforeach
    @endif

    {{-- Aucun planning --}}
    @if($plannings->count() == 0)
    <div class="alert alert-info" style="margin-top: 20px;">
        <strong></strong> Aucun planning généré. Allez d'abord sur
        <a href="{{ url('/planning') }}"> Planning</a> pour générer.
    </div>
    @endif

</div>
@endsection