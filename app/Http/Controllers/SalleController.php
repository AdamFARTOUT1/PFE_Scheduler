<?php

namespace App\Http\Controllers;

use App\Models\Salle;
use Illuminate\Http\Request;

class SalleController extends Controller
{
    public function index()
    {
        $salles = Salle::all();
        return view('salles.index', compact('salles'));
    }

    public function create()
    {
        return view('salles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:50|unique:salles',
            'type' => 'required|in:Salle,Amphi',
        ]);

        Salle::create($validated);

        return redirect()->route('salles.index')->with('success', 'Salle créée avec succès !');
    }

    public function edit(Salle $salle)
    {
        return view('salles.edit', compact('salle'));
    }

    public function update(Request $request, Salle $salle)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:50|unique:salles,nom,' . $salle->id,
            'type' => 'required|in:Salle,Amphi',
        ]);

        $salle->update($validated);

        return redirect()->route('salles.index')->with('success', 'Salle mise à jour avec succès !');
    }

    public function destroy(Salle $salle)
    {
        $salle->delete();
        return redirect()->route('salles.index')->with('success', 'Salle supprimée avec succès !');
    }
}
