<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 
        'prenom', 
        'filiere', 
        'langue', 
        'professeur_id'
    ];
    
    public function professeur()
    {
        // On lie l'étudiant au professeur via la colonne 'professeur_id'
        return $this->belongsTo(Professeur::class, 'professeur_id');
    }
}