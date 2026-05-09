@extends('layout.app')

@section('titre', 'Dashboard')

@section('contenu')
<div class="container-fluid" style="padding: 30px;">

    <h2 class="titre-page"> Dashboard</h2>

    {{-- 4 cartes chiffres clés --}}
    <div class="row">
        <div class="col-md-3">
            <div class="carte-stat">
                <div class="nombre">{{ $totalEtudiants }}</div>
                <div class="label"> Étudiants</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="carte-stat">
                <div class="nombre">{{ $totalProfs }}</div>
                <div class="label"> Professeurs</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="carte-stat">
                <div class="nombre">{{ $totalSoutenances }}</div>
                <div class="label"> Soutenances</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="carte-stat">
                <div class="nombre">{{ $totalSalles }}</div>
                <div class="label"> Salles</div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 30px;">

        {{-- Soutenances par filière --}}
        <div class="col-md-4">
            <div class="panel panel-default" style="border-radius:8px;">
                <div class="panel-heading" style="background:#1a252f; color:#fff; border-radius:8px 8px 0 0;">
                    <strong> Soutenances par filière</strong>
                </div>
                <div class="panel-body">
                    @forelse($soutenancesParFiliere as $filiere)
                    <div style="margin-bottom: 15px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                            <span class="badge-{{ strtolower($filiere->filiere) }}">{{ $filiere->filiere }}</span>
                            <strong>{{ $filiere->total }}</strong>
                        </div>
                        <div class="barre-conteneur">
                            <div class="barre" style="width: {{ $totalEtudiants > 0 ? ($filiere->total / $totalEtudiants) * 100 : 0 }}%">
                                {{ $totalEtudiants > 0 ? round(($filiere->total / $totalEtudiants) * 100) : 0 }}%
                            </div>
                        </div>
                    </div>
                    @empty
                    <p style="color:#999;">Aucune donnée.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Étudiants encadrés par professeur --}}
        <div class="col-md-4">
            <div class="panel panel-default" style="border-radius:8px;">
                <div class="panel-heading" style="background:#1a252f; color:#fff; border-radius:8px 8px 0 0;">
                    <strong> Étudiants encadrés par prof</strong>
                </div>
                <div class="panel-body">
                    @forelse($etudiantsParProf as $prof)
                    @if($prof->etudiants_count > 0)
                    <div style="margin-bottom: 10px;">
                        <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:3px;">
                            <span>{{ $prof->nom }} {{ $prof->prenom }}</span>
                            <strong>{{ $prof->etudiants_count }}</strong>
                        </div>
                        <div class="barre-conteneur">
                            @php
                                $pourcent = $maxEtudiants > 0 ? ($prof->etudiants_count / $maxEtudiants) * 100 : 0;
                                $couleur = $prof->etudiants_count > 4 ? 'rouge' : ($prof->etudiants_count < 3 ? 'orange' : '');
                            @endphp
                            <div class="barre {{ $couleur }}" style="width: {{ $pourcent }}%">
                                {{ $prof->etudiants_count }}
                            </div>
                        </div>
                    </div>
                    @endif
                    @empty
                    <p style="color:#999;">Aucune donnée.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Soutenances par professeur --}}
        <div class="col-md-4">
            <div class="panel panel-default" style="border-radius:8px;">
                <div class="panel-heading" style="background:#1a252f; color:#fff; border-radius:8px 8px 0 0;">
                    <strong> Soutenances par professeur</strong>
                </div>
                <div class="panel-body">
                    @forelse($soutenancesParProf as $prof)
                    @if($prof->plannings_as_encadrant_count > 0)
                    <div style="margin-bottom: 10px;">
                        <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:3px;">
                            <span>{{ $prof->nom }} {{ $prof->prenom }}</span>
                            <strong>{{ $prof->plannings_as_encadrant_count }}</strong>
                        </div>
                        <div class="barre-conteneur">
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
                    <p style="color:#999;">Aucune donnée.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@endsection