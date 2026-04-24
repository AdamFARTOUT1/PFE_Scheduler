<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Professeur extends Model
{
    public function etudiantsEncadres() { return $this->hasMany(Etudiant::class);
}
}
