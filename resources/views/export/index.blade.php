<!DOCTYPE html>
<html>
<head>
    <title>Exporter le Planning</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
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
        h2 {
            color: #333;
            margin-top: 30px;
            font-size: 18px;
            border-bottom: 2px solid #2196F3;
            padding-bottom: 10px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
        .panel {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .panel h3 {
            margin-top: 0;
            color: #333;
            font-size: 16px;
            border-bottom: 2px solid #2196F3;
            padding-bottom: 10px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-right: 10px;
        }
        .badge-id {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        .badge-tdai, .badge-tdia {
            background-color: #f3e5f5;
            color: #6a1b9a;
        }

        /* Export card */
        .export-card {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            margin-bottom: 30px;
            border-left: 4px solid #4CAF50;
        }
        .export-card .icon {
            font-size: 56px;
            margin-bottom: 10px;
        }
        .export-card h3 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 20px;
        }
        .export-card p {
            color: #666;
            font-size: 14px;
            margin: 0 0 20px 0;
        }
        .btn-export {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .btn-export:hover {
            background-color: #45a049;
            color: white;
            text-decoration: none;
        }
        .btn-export.disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        /* Details grid */
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .detail-item .check {
            color: #4CAF50;
            font-weight: bold;
        }

        /* Preview table */
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
            font-size: 13px;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        table tbody tr:hover {
            background-color: #f9f9f9;
        }
        .preview-info {
            text-align: center;
            padding: 10px;
            color: #999;
            font-size: 13px;
            font-style: italic;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }
        .empty-state .icon {
            font-size: 56px;
            margin-bottom: 10px;
        }
        .btn-planning {
            display: inline-block;
            padding: 12px 25px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            transition: background-color 0.3s;
            margin-top: 15px;
        }
        .btn-planning:hover {
            background-color: #1976D2;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="{{ route('dashboard.index') }}">🏠 Tableau de bord</a>
            <a href="{{ route('export.index') }}" class="active">📤 Export</a>
            <a href="{{ route('planning.index') }}">📅 Planning</a>
            <a href="{{ route('verification.index') }}">✓ Vérification</a>
            <a href="{{ route('import.index') }}">📥 Importation</a>
        </div>

        <h1>📤 Exporter le Planning</h1>

        @if ($message = session('success'))
            <div style="padding: 15px; border-radius: 4px; margin-bottom: 20px; background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724;">
                <strong>✓ Succès :</strong> {{ $message }}
            </div>
        @endif

        @if ($message = session('error'))
            <div style="padding: 15px; border-radius: 4px; margin-bottom: 20px; background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;">
                <strong>✗ Erreur :</strong> {{ $message }}
            </div>
        @endif

        @if($plannings->count() > 0)

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
                <div class="stat-card">
                    <h3>{{ $filieres->count() }}</h3>
                    <p>Filières</p>
                </div>
            </div>

            <!-- Carte d'export -->
            <div class="export-card">
                <div class="icon">📊</div>
                <h3>Télécharger le Planning Excel</h3>
                <p>Fichier Excel (.xlsx) avec style professionnel, prêt à imprimer.</p>
                <a href="{{ route('export.download') }}" class="btn-export">⬇️ Télécharger le fichier Excel</a>
            </div>

            <!-- Détails -->
            <div class="grid">
                <div class="panel">
                    <h3>📋 Contenu du fichier</h3>
                    <div class="detail-item">
                        <span><span class="check">✓</span> Planning complet (toutes les soutenances)</span>
                    </div>
                    @foreach($jours as $jour)
                    <div class="detail-item">
                        <span><span class="check">✓</span> Feuille dédiée : {{ $jour }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="panel">
                    <h3>📌 Colonnes incluses</h3>
                    <div class="detail-item">
                        <span><span class="check">✓</span> Jour & horaire (début / fin)</span>
                    </div>
                    <div class="detail-item">
                        <span><span class="check">✓</span> Salle attribuée</span>
                    </div>
                    <div class="detail-item">
                        <span><span class="check">✓</span> Nom & prénom de l'étudiant</span>
                    </div>
                    <div class="detail-item">
                        <span><span class="check">✓</span> Filière (ID / TDIA)</span>
                    </div>
                    <div class="detail-item">
                        <span><span class="check">✓</span> Encadrant & membres du jury</span>
                    </div>
                </div>
            </div>

            <!-- Aperçu -->
            <h2>👁️ Aperçu ({{ min(10, $plannings->count()) }} premières lignes)</h2>
            <table>
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
                        <th>Jury 4</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($plannings->take(10) as $p)
                    <tr>
                        <td>{{ $p->creneau->date_pfe ?? '-' }}</td>
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
                        <td>{{ $p->jury4->nom ?? '-' }} {{ $p->jury4->prenom ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($plannings->count() > 10)
                <div class="preview-info">
                    ... et {{ $plannings->count() - 10 }} autres soutenances dans le fichier exporté
                </div>
            @endif

        @else

            <div class="empty-state">
                <div class="icon">📭</div>
                <h3>Aucun planning à exporter</h3>
                <p>Vous devez d'abord générer le planning des soutenances.</p>
                <a href="{{ route('planning.index') }}" class="btn-planning">📅 Aller au Planning</a>
            </div>

        @endif
    </div>
</body>
</html>
