<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Creneau extends Model
{
    protected $table = 'creneaus';

    protected $fillable = [
        'jour',
        'heure_debut',
        'heure_fin',
    ];
}
