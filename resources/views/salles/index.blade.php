<!DOCTYPE html>
<html>
<head>
    <title>Gestion des Salles</title>
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
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .btn-create {
            padding: 12px 25px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn-create:hover {
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
        .badge-salle {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        .badge-amphi {
            background-color: #f3e5f5;
            color: #6a1b9a;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .btn-edit, .btn-delete {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-edit {
            background-color: #2196F3;
            color: white;
        }
        .btn-edit:hover {
            background-color: #1976D2;
        }
        .btn-delete {
            background-color: #f44336;
            color: white;
        }
        .btn-delete:hover {
            background-color: #da190b;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="{{ route('dashboard.index') }}">🏠 Tableau de bord</a>
            <a href="{{ route('salles.index') }}" class="active">🏢 Salles</a>
            <a href="{{ route('planning.index') }}">📅 Planning</a>
            <a href="{{ route('verification.index') }}">✓ Vérification</a>
            <a href="{{ route('import.index') }}">📥 Importation</a>
        </div>

        <h1>🏢 Gestion des Salles</h1>

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
                <h3>{{ count($salles) }}</h3>
                <p>Total salles</p>
            </div>
            <div class="stat-card">
                <h3>{{ count($salles->where('type', 'Salle')) }}</h3>
                <p>Salles</p>
            </div>
            <div class="stat-card">
                <h3>{{ count($salles->where('type', 'Amphi')) }}</h3>
                <p>Amphithéâtres</p>
            </div>
        </div>

        <!-- Header avec bouton créer -->
        <div class="header-actions">
            <h2 style="margin: 0;">Liste des Salles</h2>
            <a href="{{ route('salles.create') }}" class="btn-create">+ Ajouter une salle</a>
        </div>

        <!-- Table des salles -->
        @if ($salles->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Date de création</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salles as $salle)
                        <tr>
                            <td><strong>{{ $salle->nom }}</strong></td>
                            <td>
                                <span class="badge {{ $salle->type === 'Amphi' ? 'badge-amphi' : 'badge-salle' }}">
                                    {{ $salle->type }}
                                </span>
                            </td>
                            <td>{{ $salle->created_at->format('d/m/Y H:i') }}</td>
                            <td style="text-align: center;">
                                <div class="actions">
                                    <a href="{{ route('salles.edit', $salle) }}" class="btn-edit">✎ Éditer</a>
                                    <form action="{{ route('salles.destroy', $salle) }}" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete">🗑️ Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <p>📭 Aucune salle enregistrée</p>
                <a href="{{ route('salles.create') }}" class="btn-create" style="display: inline-block; margin-top: 20px;">+ Créer la première salle</a>
            </div>
        @endif
    </div>
</body>
</html>
