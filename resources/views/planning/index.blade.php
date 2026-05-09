@extends('layout.app')

@section('titre', 'Planning des soutenances')

@section('contenu')
<div class="container-fluid" style="padding: 20px 30px;">

    <h2 class="titre-page"> Planning des soutenances</h2>

    {{-- Bouton générer --}}
    <div style="margin-bottom: 20px;">
        <form method="POST" action="{{ url('/planning/generer') }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn-generer">
                 Générer le planning
            </button>
        </form>
        <span id="compteur" style="margin-left: 15px; color: #7f8c8d; font-size: 13px;">
            {{ $plannings->count() }} soutenance(s)
        </span>
    </div>

    {{-- Filtres --}}
    <div class="filtres">
        <label>Jour :</label>
        <select id="filtre-jour" class="form-control" style="display:inline-block; width:auto;">
            <option value="tous">Tous</option>
            @foreach($jours as $jour)
            <option value="{{ $jour }}">{{ $jour }}</option>
            @endforeach
        </select>

        <label style="margin-left: 15px;">Salle :</label>
        <select id="filtre-salle" class="form-control" style="display:inline-block; width:auto;">
            <option value="tous">Toutes</option>
            @foreach($salles as $salle)
            <option value="{{ $salle->nom }}">{{ $salle->nom }}</option>
            @endforeach
        </select>

        <label style="margin-left: 15px;">Filière :</label>
        <select id="filtre-filiere" class="form-control" style="display:inline-block; width:auto;">
            <option value="tous">Toutes</option>
            @foreach($filieres as $filiere)
            <option value="{{ $filiere }}">{{ $filiere }}</option>
            @endforeach
        </select>
    </div>

    {{-- Tableau planning --}}
    @if($plannings->count() > 0)
    <div class="table-responsive">
        <table class="table table-bordered table-hover" id="tableau-planning">
            <thead>
                <tr>
                    <th>Jour</th>
                    <th>Horaire</th>
                    <th>Salle</th>
                    <th>Étudiant</th>
                    <th>Filière</th>
                    <th>Encadrant</th>
                    <th>Jury 2</th>
                    <th>Jury 3</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plannings as $p)
                <tr
                    data-jour="{{ $p->creneau->jour ?? '' }}"
                    data-salle="{{ $p->salle->nom ?? '' }}"
                    data-filiere="{{ $p->etudiant->filiere ?? '' }}"
                >
                    <td>{{ $p->creneau->jour ?? '-' }}</td>
                    <td>{{ $p->creneau->heure_debut ?? '-' }} — {{ $p->creneau->heure_fin ?? '-' }}</td>
                    <td>{{ $p->salle->nom ?? '-' }}</td>
                    <td>
                        <div class="cellule-{{ strtolower($p->etudiant->filiere ?? '') }}">
                            {{ $p->etudiant->nom ?? '-' }} {{ $p->etudiant->prenom ?? '' }}
                        </div>
                    </td>
                    <td>
                        <span class="badge-{{ strtolower($p->etudiant->filiere ?? '') }}">
                            {{ $p->etudiant->filiere ?? '-' }}
                        </span>
                    </td>
                    <td>{{ $p->encadrant->nom ?? '-' }} {{ $p->encadrant->prenom ?? '' }}</td>
                    <td>{{ $p->jury2->nom ?? '-' }} {{ $p->jury2->prenom ?? '' }}</td>
                    <td>{{ $p->jury3->nom ?? '-' }} {{ $p->jury3->prenom ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="alert alert-info" style="margin-top: 20px;">
        <strong></strong> Aucun planning généré pour le moment. Cliquez sur <strong>"Générer le planning"</strong>.
    </div>
    @endif

</div>
@endsection