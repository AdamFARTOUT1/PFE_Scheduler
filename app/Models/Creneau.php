<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Creneau extends Model
{
    protected $table = 'creneaux';

    protected $fillable = [
        'date_pfe',
        'heure_debut',
        'heure_fin',
    ];
}
