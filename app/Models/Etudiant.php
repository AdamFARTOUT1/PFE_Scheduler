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
        'Encadrant_id'
    ];
    
    public function encadrant()
    {
        return $this->belongsTo(Professeur::class, 'Encadrant_id');
    }

    public function planning()
    {
        return $this->hasOne(Planning::class, 'etudiant_id');
    }
}