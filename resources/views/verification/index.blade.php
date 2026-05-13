<!DOCTYPE html>
<html>
<head>
    <title>Vérification des Contraintes</title>
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
            border-bottom: 2px solid #4CAF50;
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
        .alert-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
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
            text-align: center;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 28px;
            margin-bottom: 5px;
        }
        .stat-card p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        .stat-card.success {
            border-left: 4px solid #4CAF50;
        }
        .stat-card.success h3 {
            color: #4CAF50;
        }
        .stat-card.error {
            border-left: 4px solid #f44336;
        }
        .stat-card.error h3 {
            color: #f44336;
        }
        .stat-card.warning {
            border-left: 4px solid #FF9800;
        }
        .stat-card.warning h3 {
            color: #FF9800;
        }
        .issue-item {
            background-color: #f9f9f9;
            border-left: 4px solid #f44336;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .issue-item.warning {
            border-left-color: #FF9800;
        }
        .issue-item strong {
            color: #333;
            display: block;
            margin-bottom: 5px;
        }
        .issue-item p {
            margin: 0;
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
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="{{ route('dashboard.index') }}">🏠 Tableau de bord</a>
            <a href="{{ route('salles.index') }}">🏢 Salles</a>
            <a href="{{ route('planning.index') }}">📅 Planning</a>
            <a href="{{ route('verification.index') }}" class="active">✓ Vérification</a>
            <a href="{{ route('import.index') }}">📥 Importation</a>
        </div>

        <h1>✓ Vérification des Contraintes</h1>

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
                <h3 style="color: #2196F3;">{{ $plannings->count() }}</h3>
                <p>Total Soutenances</p>
            </div>
        </div>

        <!-- Erreurs critiques -->
        @if(count($erreurs) > 0)
        <h2>❌ Erreurs Critiques ({{ count($erreurs) }})</h2>
        <div>
            @foreach($erreurs as $erreur)
                <div class="issue-item">
                    <strong>{{ $erreur['titre'] }}</strong>
                    <p>{{ $erreur['detail'] }}</p>
                </div>
            @endforeach
        </div>
        @else
        <h2>✓ Erreurs Critiques</h2>
        <div class="alert alert-success">
            <strong>Excellent !</strong> Aucune erreur critique détectée.
        </div>
        @endif

        <!-- Avertissements -->
        @if(count($warnings) > 0)
        <h2>⚠️ Avertissements ({{ count($warnings) }})</h2>
        <div>
            @foreach($warnings as $warning)
                <div class="issue-item warning">
                    <strong>{{ $warning['titre'] }}</strong>
                    <p>{{ $warning['detail'] }}</p>
                </div>
            @endforeach
        </div>
        @else
        <h2>⚠️ Avertissements</h2>
        <div class="alert alert-success">
            <strong>Excellent !</strong> Aucun avertissement.
        </div>
        @endif

        <!-- État de vérification global -->
        <h2>📊 Résumé</h2>
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
</body>
</html>