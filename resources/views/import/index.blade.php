<!DOCTYPE html>
<html>
<head>
    <title>Importation des Données</title>
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
            border-left: 4px solid #FF9800;
            text-align: center;
        }
        .stat-card strong {
            display: block;
            color: #FF9800;
            font-size: 20px;
            margin-bottom: 5px;
        }
        .stat-card span {
            color: #666;
            font-size: 13px;
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
        .import-section {
            background-color: #f0f7ff;
            border: 2px solid #2196F3;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
        }
        .import-section h2 {
            color: #1976D2;
            border-bottom: none;
            margin-top: 0;
        }
        .info-box {
            background-color: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            font-size: 14px;
        }
        .info-box strong {
            color: #1565c0;
        }
        .info-box ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .info-box li {
            margin: 5px 0;
        }
        button {
            padding: 12px 30px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #1976D2;
        }
        input[type="file"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 10px 0;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
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
        }
        table tbody tr:hover {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="{{ route('dashboard.index') }}">🏠 Tableau de bord</a>
            <a href="{{ route('salles.index') }}">🏢 Salles</a>
            <a href="{{ route('planning.index') }}">📅 Planning</a>
            <a href="{{ route('verification.index') }}">✓ Vérification</a>
            <a href="{{ route('import.index') }}" class="active">📥 Importation</a>
        </div>

        <h1>📥 Importation des Données</h1>

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
                <strong>{{ $stats['total_etudiants'] }}</strong>
                <span>Étudiants</span>
            </div>
            <div class="stat-card">
                <strong>{{ $stats['total_professeurs'] }}</strong>
                <span>Professeurs</span>
            </div>
            @foreach ($stats['par_filiere'] as $fil)
                <div class="stat-card">
                    <strong>{{ $fil->total }}</strong>
                    <span>{{ $fil->filiere }}</span>
                </div>
            @endforeach
        </div>

        <!-- Section d'importation -->
        <div class="import-section">
            <h2>🚀 Importer les Données</h2>

            <div class="info-box">
                <strong>📋 Format du fichier Excel requis :</strong><br>
                Votre fichier doit contenir exactement 3 feuilles :
                <ul>
                    <li><strong>Salles</strong> - Colonnes : (A) Nom, (B) Type (Salle/Amphi)</li>
                    <li><strong>Professeurs</strong> - Colonnes : (B) Nom, (C) Prénom, (D) Spécialité</li>
                    <li><strong>Étudiants</strong> - Colonnes : (A) Nom, (B) Prénom, (C) Filière (TDIA/ID), (D) Langue (FR/AN)</li>
                </ul>
            </div>

            <form action="{{ route('import.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <label for="file_unified">📁 Sélectionner le fichier Excel unifié :</label>
                <input type="file" name="file_unified" id="file_unified" accept=".xlsx,.xls" required>

                <br><br>
                <button type="submit">🔄 Importer toutes les données</button>
            </form>
        </div>

        <!-- Statistiques d'encadrement -->
        <h2>📊 Répartition des Encadrements</h2>
        <table>
            <thead>
                <tr>
                    <th>Professeur</th>
                    <th style="text-align: center;">Étudiants Encadrés</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($stats['encadrement_pfe'] as $prof)
                    <tr>
                        <td><strong>{{ $prof->nom }} {{ $prof->prenom }}</strong></td>
                        <td style="text-align: center;">{{ $prof->etudiants_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
