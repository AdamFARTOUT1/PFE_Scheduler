<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plannings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etudiant_id')->unique()->constrained('etudiants');
            $table->foreignId('salle_id')->constrained('salles');
            $table->foreignId('creneau_id')->constrained('creneaux');
            $table->foreignId('encadrant_id')->constrained('professeurs');
            $table->foreignId('jury2_id')->constrained('professeurs');
            $table->foreignId('jury3_id')->constrained('professeurs');
            $table->unique(['salle_id', 'creneau_id'], 'unique_salle_moment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plannings');
    }
};
