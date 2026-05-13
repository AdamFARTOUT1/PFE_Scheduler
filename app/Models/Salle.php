<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    protected $fillable = ['nom', 'type'];

    public function plannings()
    {
        return $this->hasMany(Planning::class);
    }
}
