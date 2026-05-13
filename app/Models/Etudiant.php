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
        return $this->belongsTo(Professeur::class, 'professeur_id');
    }
}