@extends('layout.app')

@section('contenu')
<div class="container">

    <h1>Planning des Soutenances</h1>

    @if ($message = session('success'))
        <div class="alert alert-success">
            <strong>✓ Succès :</strong> {{ $message }}
        </div>
    @endif

    @if ($message = session('error'))
        <div class="alert alert-error">
            <strong>✗ Erreur :</strong> {{ $message }}
        </div>
    @endif

    <!-- Statistiques -->
    <div class="stats">
        <div class="stat-card">
            <h3>{{ $plannings->count() }}</h3>
            <p>Soutenances</p>
        </div>
        <div class="stat-card">
            <h3>{{ $jours->count() }}</h3>
            <p>Jours</p>
        </div>
        <div class="stat-card">
            <h3>{{ $salles->count() }}</h3>
            <p>Salles</p>
        </div>
    </div>

    <!-- Header avec bouton générer -->
    <div class="header-actions">
        <h2 style="margin: 0;">Liste du Planning</h2>
        <form method="POST" action="{{ url('/planning/generer') }}" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            @csrf
            <label for="date_debut" style="font-weight: bold; color: #333; white-space: nowrap;">Date de début :</label>
            <input type="date" name="date_debut" id="date_debut"
                   value="{{ old('date_debut', date('Y-m-d')) }}"
                   required
                   style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
            <label for="duree_jours" style="font-weight: bold; color: #333; white-space: nowrap;">Durée :</label>
            <input type="number" name="duree_jours" id="duree_jours"
                   value="{{ old('duree_jours', 3) }}"
                   min="1" max="10" required
                   style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; width: 70px;">
            <span style="color: #666; font-size: 13px; white-space: nowrap;">jours</span>
            <label for="heure_debut" style="font-weight: bold; color: #333; white-space: nowrap;">De :</label>
            <input type="time" name="heure_debut" id="heure_debut"
                   value="{{ old('heure_debut', '09:00') }}"
                   required
                   style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
            <label for="heure_fin" style="font-weight: bold; color: #333; white-space: nowrap;">À :</label>
            <input type="time" name="heure_fin" id="heure_fin"
                   value="{{ old('heure_fin', '18:00') }}"
                   required
                   style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
            <span style="color: #666; font-size: 12px; white-space: nowrap;">(pause 12h-14h auto)</span>
            <button type="submit" class="btn-generer">Générer le planning</button>
        </form>
    </div>

    <!-- Filtres -->
    @if($plannings->count() > 0)
    <div class="filtres">
        <div>
            <label>Jour :</label>
            <select id="filtre-jour">
                <option value="tous">Tous</option>
                @foreach($jours as $jour)
                <option value="{{ $jour }}">{{ $jour }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label>Salle :</label>
            <select id="filtre-salle">
                <option value="tous">Toutes</option>
                @foreach($salles as $salle)
                <option value="{{ $salle->nom }}">{{ $salle->nom }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label>Filière :</label>
            <select id="filtre-filiere">
                <option value="tous">Toutes</option>
                @foreach($filieres as $filiere)
                <option value="{{ $filiere }}">{{ $filiere }}</option>
                @endforeach
            </select>
        </div>
    </div>
    @endif

    <!-- Table du planning -->
    @if($plannings->count() > 0)
        <table id="tableau-planning">
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
                    data-jour="{{ $p->creneau->date_pfe ?? '' }}"
                    data-salle="{{ $p->salle->nom ?? '' }}"
                    data-filiere="{{ $p->etudiant->filiere ?? '' }}"
                >
                    <td>{{ $p->creneau->date_pfe ?? '-' }}</td>
                    <td>{{ $p->creneau ? \Carbon\Carbon::parse($p->creneau->heure_debut)->format('H:i') : '-' }} - {{ $p->creneau ? \Carbon\Carbon::parse($p->creneau->heure_fin)->format('H:i') : '-' }}</td>
                    <td>{{ $p->salle->nom ?? '-' }}</td>
                    <td><strong>{{ $p->etudiant->nom ?? '-' }} {{ $p->etudiant->prenom ?? '' }}</strong></td>
                    <td>
                        <span class="badge badge-{{ strtolower($p->etudiant->filiere ?? '') }}">
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
    @else
        <div class="alert alert-info">
            <p>Aucun planning généré. Veuillez utiliser le formulaire ci-dessus pour générer un planning.</p>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filtre_jour = document.getElementById('filtre-jour');
        const filtre_salle = document.getElementById('filtre-salle');
        const filtre_filiere = document.getElementById('filtre-filiere');
        const tbody = document.querySelector('#tableau-planning tbody');

        function filterTable() {
            if (!tbody) return;

            const jourVal = filtre_jour ? filtre_jour.value : 'tous';
            const salleVal = filtre_salle ? filtre_salle.value : 'tous';
            const filiereVal = filtre_filiere ? filtre_filiere.value : 'tous';

            tbody.querySelectorAll('tr').forEach(row => {
                const jour = row.dataset.jour;
                const salle = row.dataset.salle;
                const filiere = row.dataset.filiere;

                const jourMatch = jourVal === 'tous' || jour === jourVal;
                const salleMatch = salleVal === 'tous' || salle === salleVal;
                const filiereMatch = filiereVal === 'tous' || filiere === filiereVal;

                row.style.display = (jourMatch && salleMatch && filiereMatch) ? '' : 'none';
            });
        }

        if (filtre_jour) filtre_jour.addEventListener('change', filterTable);
        if (filtre_salle) filtre_salle.addEventListener('change', filterTable);
        if (filtre_filiere) filtre_filiere.addEventListener('change', filterTable);
    });
</script>
@endsection
