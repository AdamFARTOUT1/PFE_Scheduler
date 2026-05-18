@extends('layout.app')

@section('titre', 'Tableau de Bord')

@section('contenu')

    <div class="container">

        <h1 class="titre-page" style="text-align: center; border-bottom: none;">Tableau de Bord</h1>
        <p style="text-align: center; color: #7f8c8d; margin-bottom: 25px;">Vue d'ensemble du système de soutenances</p>

        <!-- Statistiques principales -->
        <div class="stats">
            <div class="stat-card">
                <h3>{{ $totalEtudiants }}</h3>
                <p>Étudiants</p>
            </div>
            <div class="stat-card">
                <h3>{{ $totalProfs }}</h3>
                <p>Professeurs</p>
            </div>
            <div class="stat-card">
                <h3>{{ $totalSoutenances }}</h3>
                <p>Soutenances</p>
            </div>
            <div class="stat-card">
                <h3>{{ $totalSalles }}</h3>
                <p>Salles</p>
            </div>
        </div>

        <!-- Sections détaillées -->
        <div class="grid">
            <!-- Étudiants par filière -->
            <div class="panel">
                <h3>Étudiants par Filière</h3>
                @forelse($etudiantsParFiliere as $filiere)
                    <div style="margin-bottom: 15px;">
                        <div class="item-row">
                            <span class="badge badge-{{ strtolower($filiere->filiere) }}">{{ $filiere->filiere }}</span>
                        </div>
                        <div class="barre-conteneur">
                            <div class="barre"
                                style="width: {{ $totalEtudiants > 0 ? ($filiere->total / $totalEtudiants) * 100 : 0 }}%">
                                {{ $totalEtudiants > 0 ? round(($filiere->total / $totalEtudiants) * 100) : 0 }}%
                            </div>
                        </div>
                    </div>
                @empty
                    <p style="color: #999;">Aucune donnée.</p>
                @endforelse
            </div>
        </div>
        <div class="grid">
            <!-- Soutenances par professeur -->
            <div class="panel">
                <h3>Soutenances par Professeur</h3>
                @forelse($soutenancesParProf as $prof)
                    @if($prof->plannings_as_encadrant_count > 0)
                        <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 15px;">
                            <span
                                style="flex: 0 0 250px; font-weight: bold; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                title="{{ $prof->nom }} {{ $prof->prenom }}">
                                {{ $prof->nom }} {{ $prof->prenom }}
                            </span>
                            <div class="barre-conteneur" style="flex-grow: 1; margin: 0;">
                                @php
                                    $pourcent = $maxSoutenances > 0 ? ($prof->plannings_as_encadrant_count / $maxSoutenances) * 100 : 0;
                                @endphp
                                <div class="barre" style="width: {{ $pourcent }}%">
                                    {{ $prof->plannings_as_encadrant_count }}
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <p style="color: #999;">Aucune donnée.</p>
                @endforelse
            </div>
        </div>

    </div>

@endsection