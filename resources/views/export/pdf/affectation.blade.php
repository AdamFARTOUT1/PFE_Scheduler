<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        h1 { text-align: center; color: #2D6ABF; font-size: 18px; margin-bottom: 5px; }
        h2 { text-align: center; color: #666; font-size: 12px; margin-bottom: 25px; }
        h3 { color: #1A4F9C; font-size: 14px; margin-top: 25px; border-bottom: 2px solid #2D6ABF; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background-color: #2D6ABF; color: #fff; padding: 8px; font-size: 10px; text-align: left; }
        td { padding: 6px 8px; border-bottom: 1px solid #ddd; font-size: 10px; }
        tr:nth-child(even) { background-color: #EBF5FB; }
        .count { color: #666; font-size: 11px; }
        .footer { text-align: center; color: #999; font-size: 8px; margin-top: 30px; }
    </style>
</head>
<body>
    <h1>Affectation des Encadrants — PFE</h1>
    <h2>ENSA Al Hoceima — 2025/2026</h2>

    @foreach($parEncadrant as $nomEncadrant => $etudiants)
    <h3>{{ $nomEncadrant }} <span class="count">({{ $etudiants->count() }} étudiants)</span></h3>
    <table>
        <thead>
            <tr>
                <th>Étudiant</th>
                <th>Filière</th>
                <th>Langue</th>
                <th>Date</th>
                <th>Horaire</th>
                <th>Salle</th>
            </tr>
        </thead>
        <tbody>
            @foreach($etudiants as $p)
            <tr>
                <td><strong>{{ $p->etudiant->nom ?? '-' }} {{ $p->etudiant->prenom ?? '' }}</strong></td>
                <td>{{ $p->etudiant->filiere ?? '-' }}</td>
                <td>{{ $p->etudiant->langue ?? '-' }}</td>
                <td>{{ $p->creneau->date_pfe ?? '-' }}</td>
                <td>{{ $p->creneau->heure_debut ?? '-' }} — {{ $p->creneau->heure_fin ?? '-' }}</td>
                <td>{{ $p->salle->nom ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endforeach

    <div class="footer">
        PFE Scheduler — ENSA Al Hoceima | Département MI
    </div>
</body>
</html>
