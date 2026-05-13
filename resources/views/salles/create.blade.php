<!DOCTYPE html>
<html>
<head>
    <title>Ajouter une Salle</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #333; 
            text-align: center;
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        input[type="text"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        input[type="text"]:focus,
        select:focus {
            outline: none;
            border-color: #2196F3;
            box-shadow: 0 0 5px rgba(33, 150, 243, 0.3);
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            justify-content: center;
        }
        button, .btn-cancel {
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
        }
        button[type="submit"]:hover {
            background-color: #45a049;
        }
        .btn-cancel {
            background-color: #9e9e9e;
            color: white;
        }
        .btn-cancel:hover {
            background-color: #757575;
        }
        .error-message {
            color: #d32f2f;
            font-size: 14px;
            margin-top: 5px;
        }
        .errors {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .errors ul {
            margin: 5px 0;
            padding-left: 20px;
        }
        .errors li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏢 Ajouter une nouvelle salle</h1>

        @if ($errors->any())
            <div class="errors">
                <strong>Erreurs de validation :</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('salles.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="nom">Nom de la salle *</label>
                <input 
                    type="text" 
                    id="nom" 
                    name="nom" 
                    value="{{ old('nom') }}"
                    placeholder="Ex: Salle A101, Amphi B2..." 
                    required
                >
                @error('nom')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="type">Type *</label>
                <select id="type" name="type" required>
                    <option value="">-- Sélectionner un type --</option>
                    <option value="Salle" {{ old('type') === 'Salle' ? 'selected' : '' }}>Salle</option>
                    <option value="Amphi" {{ old('type') === 'Amphi' ? 'selected' : '' }}>Amphithéâtre</option>
                </select>
                @error('type')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit">✓ Créer la salle</button>
                <a href="{{ route('salles.index') }}" class="btn-cancel">✕ Annuler</a>
            </div>
        </form>
    </div>
</body>
</html>
