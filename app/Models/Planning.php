<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Planning extends Model
{
    public function etudiant()  { return $this->belongsTo(Etudiant::class); }
    public function encadrant() { return $this->belongsTo(Professeur::class, 'encadrant_id'); }
    public function jury2()     { return $this->belongsTo(Professeur::class, 'jury2_id'); }
    public function jury3()     { return $this->belongsTo(Professeur::class, 'jury3_id'); }
    public function creneau()   { return $this->belongsTo(Creneau::class);}
    public function salle()     { return $this->belongsTo(Salle::class);}
}
