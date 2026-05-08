<!DOCTYPE html>
<html>
<head>
    <title>Test Import</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .filiere-section { margin: 20px 0; padding: 15px; border: 2px solid #ddd; border-radius: 5px; }
        .profs-section { background-color: #f0f0f0; border-color: #666; }
        h2 { color: #333; margin-top: 0; }
        button { padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #45a049; }
        input[type="file"] { margin: 10px 0; }
        label { display: block; margin-top: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>📚 Importation des Données</h1>
    <p><strong>Total étudiants actuel :</strong> {{ $stats['total_etudiants'] }}</p>
    
    <!-- Étape 1: Fichier Professeurs (Commun) -->
    <div class="filiere-section profs-section">
        <h2>⭐ Étape 1 : Importer les Professeurs (Commun aux deux filières)</h2>
        <form action="{{ route('import.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="filiere" value="PROFS_ONLY">
            
            <label for="file_profs">Sélectionner le fichier des professeurs:</label>
            <input type="file" name="file_profs" id="file_profs" accept=".xlsx,.xls" required>
            <br><br>
            
            <button type="submit">Importer les Professeurs</button>
        </form>
    </div>
    
    <!-- Étape 2: Section TDIA -->
    <div class="filiere-section">
        <h2>🎓 Étape 2.1 : Importer les Étudiants TDIA</h2>
        <form action="{{ route('import.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="filiere" value="TDIA">
            
            <label for="file_etudiants_tdia">Fichier Étudiants TDIA:</label>
            <input type="file" name="file_etudiants" id="file_etudiants_tdia" accept=".xlsx,.xls" required>
            <br><br>
            
            <button type="submit">Importer Étudiants TDIA</button>
        </form>
    </div>
    
    <!-- Étape 3: Section ID -->
    <div class="filiere-section">
        <h2>💻 Étape 2.2 : Importer les Étudiants ID</h2>
        <form action="{{ route('import.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="filiere" value="ID">
            
            <label for="file_etudiants_id">Fichier Étudiants ID:</label>
            <input type="file" name="file_etudiants" id="file_etudiants_id" accept=".xlsx,.xls" required>
            <br><br>
            
            <button type="submit">Importer Étudiants ID</button>
        </form>
    </div>
</body>
</html>