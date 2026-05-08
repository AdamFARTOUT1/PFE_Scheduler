<?php

use Illuminate\Support\Facades\Route;
<<<<<<< Updated upstream
use App\Http\Controllers\ImportController;
Route::get('/import', [ImportController::class, 'index'])->name('import.index');
Route::post('/import', [ImportController::class, 'store'])->name('import.store');
=======
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\VerificationController;

>>>>>>> Stashed changes
Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

Route::get('/planning', [PlanningController::class, 'index'])->name('planning.index');
Route::post('/planning/generer', [PlanningController::class, 'generer'])->name('planning.generer');

Route::get('/verification', [VerificationController::class, 'index'])->name('verification.index');
