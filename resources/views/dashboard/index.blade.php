<!DOCTYPE html>
<html>
<head>
    <title>Tableau de Bord</title>
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
        .barre-conteneur {
            background-color: #e0e0e0;
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
            margin: 8px 0;
        }
        .barre {
            background-color: #2196F3;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 11px;
            font-weight: bold;
            min-width: 30px;
        }
        .barre.rouge {
            background-color: #f44336;
        }
        .barre.orange {
            background-color: #FF9800;
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
        .badge-tdai {
            background-color: #f3e5f5;
            color: #6a1b9a;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="{{ route('dashboard.index') }}" class="active">🏠 Tableau de bord</a>
            <a href="{{ route('salles.index') }}">🏢 Salles</a>
            <a href="{{ route('planning.index') }}">📅 Planning</a>
            <a href="{{ route('verification.index') }}">✓ Vérification</a>
            <a href="{{ route('import.index') }}">📥 Importation</a>
        </div>

        <h1>🏠 Tableau de Bord</h1>

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
                <h3>📚 Étudiants par Filière</h3>
                @forelse($etudiantsParFiliere as $filiere)
                <div style="margin-bottom: 15px;">
                    <div class="item-row">
                        <span class="badge badge-{{ strtolower($filiere->filiere) }}">{{ $filiere->filiere }}</span>
                        <strong>{{ $filiere->total }}</strong>
                    </div>
                    <div class="barre-conteneur">
                        <div class="barre" style="width: {{ $totalEtudiants > 0 ? ($filiere->total / $totalEtudiants) * 100 : 0 }}%">
                            {{ $totalEtudiants > 0 ? round(($filiere->total / $totalEtudiants) * 100) : 0 }}%
                        </div>
                    </div>
                </div>
                @empty
                <p style="color: #999;">Aucune donnée.</p>
                @endforelse
            </div>

            <!-- Étudiants encadrés par professeur -->
            <div class="panel">
                <h3>👨‍🏫 Étudiants Encadrés</h3>
                @forelse($etudiantsParProf as $prof)
                @if($prof->etudiants_count > 0)
                <div style="margin-bottom: 10px;">
                    <div class="item-row">
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
                <p style="color: #999;">Aucune donnée.</p>
                @endforelse
            </div>

            <!-- Soutenances par professeur -->
            <div class="panel">
                <h3>📅 Soutenances par Professeur</h3>
                @forelse($soutenancesParProf as $prof)
                @if($prof->plannings_as_encadrant_count > 0)
                <div style="margin-bottom: 10px;">
                    <div class="item-row">
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
                <p style="color: #999;">Aucune donnée.</p>
                @endforelse
            </div>
        </div>
    </div>
</body>
</html>