<?php

namespace App\Http\Controllers;

use App\Models\Professeur;
use Illuminate\Http\Request;

class ProfesseurController extends Controller
{
    public function index()
    {
        $professeurs = Professeur::withCount('etudiants')->get();
        return view('professeurs.index', compact('professeurs'));
    }

    public function create()
    {
        return view('professeurs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'specialite' => 'nullable|string|max:255',
        ]);

        Professeur::create($validated);

        return redirect()->route('professeurs.index')->with('success', 'Professeur créé avec succès !');
    }

    public function edit(Professeur $professeur)
    {
        return view('professeurs.edit', compact('professeur'));
    }

    public function update(Request $request, Professeur $professeur)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'specialite' => 'nullable|string|max:255',
        ]);

        $professeur->update($validated);

        return redirect()->route('professeurs.index')->with('success', 'Professeur mis à jour avec succès !');
    }

    public function destroy(Professeur $professeur)
    {
        $professeur->delete();
        return redirect()->route('professeurs.index')->with('success', 'Professeur supprimé avec succès !');
    }
}
