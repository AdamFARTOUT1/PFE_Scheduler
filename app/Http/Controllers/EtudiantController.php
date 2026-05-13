<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\Professeur;
use Illuminate\Http\Request;

class EtudiantController extends Controller
{
    public function index()
    {
        $etudiants = Etudiant::all();
        $stats = [
            'total' => Etudiant::count(),
            'tdia' => Etudiant::where('filiere', 'TDIA')->count(),
            'id' => Etudiant::where('filiere', 'ID')->count(),
        ];
        return view('etudiants.index', compact('etudiants', 'stats'));
    }

    public function create()
    {
        $professeurs = Professeur::all();
        return view('etudiants.create', compact('professeurs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'filiere' => 'required|in:TDIA,ID',
            'langue' => 'required|in:FR,AN',
            'professeur_id' => 'nullable|exists:professeurs,id',
        ]);

        Etudiant::create($validated);

        return redirect()->route('etudiants.index')->with('success', 'Étudiant créé avec succès !');
    }

    public function edit(Etudiant $etudiant)
    {
        $professeurs = Professeur::all();
        return view('etudiants.edit', compact('etudiant', 'professeurs'));
    }

    public function update(Request $request, Etudiant $etudiant)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'filiere' => 'required|in:TDIA,ID',
            'langue' => 'required|in:FR,AN',
            'professeur_id' => 'nullable|exists:professeurs,id',
        ]);

        $etudiant->update($validated);

        return redirect()->route('etudiants.index')->with('success', 'Étudiant mis à jour avec succès !');
    }

    public function destroy(Etudiant $etudiant)
    {
        $etudiant->delete();
        return redirect()->route('etudiants.index')->with('success', 'Étudiant supprimé avec succès !');
    }
}
