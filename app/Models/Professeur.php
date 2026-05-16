<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professeur extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'prenom', 'specialite','nb_jurys','nb_encadres'];

    public function etudiants()
    {
        return $this->hasMany(Etudiant::class, 'professeur_ID');
    }

    public function planningsAsEncadrant()
    {
        return $this->hasMany(Planning::class, 'encadrant_id');
    }

    public function planningsJury2()
    {
        return $this->hasMany(Planning::class, 'jury2_id');
    }

    public function planningsJury3()
    {
        return $this->hasMany(Planning::class, 'jury3_id');
    }

    public function planningsJury4()
    {
        return $this->hasMany(Planning::class, 'jury4_id');
    }
}