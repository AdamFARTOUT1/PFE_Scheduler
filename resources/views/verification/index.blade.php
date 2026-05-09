@extends('layout.app')

@section('titre', 'Dashboard')

@section('contenu')
<div class="container-fluid" style="padding: 20px 30px;">

    <h2 class="titre-page"> Dashboard</h2>

    {{-- 4 cartes chiffres clés --}}
    <div class="row">
        <div class="col-md-3">
            <div class="carte-stat">
                <div class="nombre">{{ $totalEtudiants }}</div>
                <div class="label">Étudiants</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="carte-stat">
                <div class="nombre">{{ $totalProfs }}</div>
                <div class="label">Professeurs</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="carte-stat">
                <div class="nombre">{{ $totalSoutenances }}</div>
                <div class="label">Soutenances</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="carte-stat">
                <div class="nombre">{{ $totalSalles }}</div>
                <div class="label">Salles</div>
            </div>
        </div>
    </div>

    {{-- Soutenances par filière --}}
    <div class="row" style="margin-top: 30px;">
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading"><strong> Soutenances par filière</strong></div>
                <div class="panel-body">
                    @foreach($soutenancesParFiliere as $filiere)
                    <p>
                        <span class="badge-{{ strtolower($filiere->filiere) }}">{{ $filiere->filiere }}</span>
                        <strong style="margin-left: 8px;">{{ $filiere->total }}</strong> étudiants
                    </p>
                    <div class="barre-conteneur">
                        <div class="barre" style="width: {{ ($filiere->total / $totalEtudiants) * 100 }}%">
                            {{ round(($filiere->total / $totalEtudiants) * 100) }}%
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Boutons export --}}
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading"><strong> Exporter</strong></div>
                <div class="panel-body">
                    <p style="margin-bottom: 10px;">
                        <a href="{{ url('/export/affectation/pdf') }}" class="btn btn-danger btn-block">
                             Affectation — PDF
                        </a>
                    </p>
                    <p style="margin-bottom: 10px;">
                        <a href="{{ url('/export/affectation/word') }}" class="btn btn-primary btn-block">
                             Affectation — Word
                        </a>
                    </p>
                    <p style="margin-bottom: 10px;">
                        <a href="{{ url('/export/planning/pdf') }}" class="btn btn-danger btn-block">
                             Planning — PDF
                        </a>
                    </p>
                    <p style="margin-bottom: 10px;">
                        <a href="{{ url('/export/planning/word') }}" class="btn btn-primary btn-block">
                             Planning — Word
                        </a>
                    </p>
                    <p>
                        <a href="{{ url('/export/pvs') }}" class="btn btn-success btn-block">
                             PVs — ZIP
                        </a>
                    </p>
                </div>
            </div>
        </div>

        {{-- Liens rapides --}}
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading"><strong>🔗 Accès rapide</strong></div>
                <div class="panel-body">
                    <p>
                        <a href="{{ url('/planning') }}" class="btn btn-default btn-block">
                             Voir le planning
                        </a>
                    </p>
                    <p>
                        <a href="{{ url('/verification') }}" class="btn btn-default btn-block">
                             Vérifier les contraintes
                        </a>
                    </p>
                    <p>
                        <a href="{{ url('/import') }}" class="btn btn-default btn-block">
                             Importer Excel
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Étudiants encadrés par prof --}}
    <div class="row" style="margin-top: 10px;">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading"><strong> Étudiants encadrés par professeur</strong></div>
                <div class="panel-body">
                    @foreach($etudiantsParProf as $prof)
                    @if($prof->etudiants_count > 0)
                    <p style="margin-bottom: 4px; font-size: 13px;">
                        {{ $prof->nom }} {{ $prof->prenom }}
                        <span class="pull-right"><strong>{{ $prof->etudiants_count }}</strong></span>
                    </p>
                    <div class="barre-conteneur">
                        @php
                            $pourcent = $maxEtudiants > 0 ? ($prof->etudiants_count / $maxEtudiants) * 100 : 0;
                            $couleur = $prof->etudiants_count > 4 ? 'rouge' : ($prof->etudiants_count < 3 ? 'orange' : '');
                        @endphp
                        <div class="barre {{ $couleur }}" style="width: {{ $pourcent }}%">
                            {{ $prof->etudiants_count }}
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Soutenances par prof --}}
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading"><strong> Soutenances par professeur</strong></div>
                <div class="panel-body">
                    @foreach($soutenancesParProf as $prof)
                    @if($prof->plannings_as_encadrant_count > 0)
                    <p style="margin-bottom: 4px; font-size: 13px;">
                        {{ $prof->nom }} {{ $prof->prenom }}
                        <span class="pull-right"><strong>{{ $prof->plannings_as_encadrant_count }}</strong></span>
                    </p>
                    <div class="barre-conteneur">
                        @php
                            $pourcent = $maxSoutenances > 0 ? ($prof->plannings_as_encadrant_count / $maxSoutenances) * 100 : 0;
                        @endphp
                        <div class="barre" style="width: {{ $pourcent }}%">
                            {{ $prof->plannings_as_encadrant_count }}
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>
@endsection