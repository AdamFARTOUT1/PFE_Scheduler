<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professeur extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'prenom', 'specialite'];

   
    public function etudiants()
    {
        return $this->hasMany(Etudiant::class, 'professeur_id');
    }
    
    public function planningsAsEncadrant()
    {
        return $this->hasMany(Planning::class, 'encadrant_id');
    }
}