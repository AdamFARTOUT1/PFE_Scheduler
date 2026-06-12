<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creneaux', function (Blueprint $table) {
            $table->id();
            $table->date('date_pfe');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->timestamps();
        }); 
    }
    
    public function down(): void
    {
        Schema::dropIfExists('crenaux');
    }
};
