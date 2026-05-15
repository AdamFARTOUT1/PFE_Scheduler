<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
        h1 { text-align: center; color: #2D6ABF; font-size: 18px; margin-bottom: 5px; }
        h2 { text-align: center; color: #666; font-size: 12px; margin-bottom: 20px; }
        h3 { color: #1A4F9C; font-size: 14px; margin-top: 25px; border-bottom: 2px solid #2D6ABF; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #2D6ABF; color: #fff; padding: 8px 5px; font-size: 9px; text-align: center; }
        td { padding: 6px 5px; border-bottom: 1px solid #ddd; font-size: 9px; text-align: center; }
        tr:nth-child(even) { background-color: #EBF5FB; }
        .badge-id { background: #3498db; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 8px; }
        .badge-tdia { background: #27ae60; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 8px; }
        .footer { text-align: center; color: #999; font-size: 8px; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Planning des Soutenances PFE</h1>
    <h2>ENSA Al Hoceima — 2025/2026</h2>

    @foreach($jours as $jour)
    <h3>{{ $jour }}</h3>
    <table>
        <thead>
            <tr>
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
            @foreach($plannings->filter(fn($p) => ($p->creneau->date_pfe ?? '') === $jour) as $p)
            <tr>
                <td>{{ $p->creneau->heure_debut ?? '-' }} — {{ $p->creneau->heure_fin ?? '-' }}</td>
                <td>{{ $p->salle->nom ?? '-' }}</td>
                <td style="text-align: left;"><strong>{{ $p->etudiant->nom ?? '-' }} {{ $p->etudiant->prenom ?? '' }}</strong></td>
                <td>
                    <span class="badge-{{ strtolower($p->etudiant->filiere ?? '') }}">
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
    @endforeach

    <div class="footer">
        PFE Scheduler — ENSA Al Hoceima | Département MI
    </div>
</body>
</html>
