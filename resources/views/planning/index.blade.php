<!DOCTYPE html>
<html>
<head>
    <title>Planning des Soutenances</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #333; 
            margin-top: 0;
            text-align: center;
        }
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .btn-create, .btn-generer {
            padding: 12px 25px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn-create:hover, .btn-generer:hover {
            background-color: #45a049;
        }
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .alert-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table thead {
            background-color: #f0f0f0;
            border-bottom: 2px solid #ddd;
        }
        table th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            color: #333;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        table tbody tr:hover {
            background-color: #f9f9f9;
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: bold;
        }
        .badge-id {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        .badge-tdai {
            background-color: #f3e5f5;
            color: #6a1b9a;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid #2196F3;
            text-align: center;
        }
        .stat-card h3 {
            margin: 0;
            color: #2196F3;
            font-size: 28px;
        }
        .stat-card p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }
        .nav {
            background-color: #333;
            padding: 0;
            margin: -30px -30px 30px -30px;
            border-radius: 8px 8px 0 0;
        }
        .nav a {
            display: inline-block;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .nav a:hover, .nav a.active {
            background-color: #555;
        }
        .filtres {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        .filtres label {
            font-weight: bold;
            color: #333;
        }
        .filtres select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="{{ route('dashboard.index') }}">🏠 Tableau de bord</a>
            <a href="{{ route('export.index') }}">📤 Export</a>
            <a href="{{ route('planning.index') }}" class="active">📅 Planning</a>
            <a href="{{ route('verification.index') }}">✓ Vérification</a>
            <a href="{{ route('import.index') }}">📥 Importation</a>
        </div>

        <h1>📅 Planning des Soutenances</h1>

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
            <form method="POST" action="{{ url('/planning/generer') }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn-generer">⚡ Générer le planning</button>
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
                        data-jour="{{ $p->creneau->jour ?? '' }}"
                        data-salle="{{ $p->salle->nom ?? '' }}"
                        data-filiere="{{ $p->etudiant->filiere ?? '' }}"
                    >
                        <td>{{ $p->creneau->jour ?? '-' }}</td>
                        <td>{{ $p->creneau->heure_debut ?? '-' }} — {{ $p->creneau->heure_fin ?? '-' }}</td>
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
            <div class="empty-state">
                <p>📭 Aucun planning généré</p>
                <form method="POST" action="{{ url('/planning/generer') }}" style="display:inline; margin-top: 20px;">
                    @csrf
                    <button type="submit" class="btn-create" style="display: inline-block; margin-top: 20px;">⚡ Générer votre premier planning</button>
                </form>
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
</body>
</html>
@endsection