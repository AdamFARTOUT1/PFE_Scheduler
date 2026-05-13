<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ExportController;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/import', [ImportController::class, 'index'])->name('import.index');
Route::post('/import', [ImportController::class, 'store'])->name('import.store');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

Route::get('/planning', [PlanningController::class, 'index'])->name('planning.index');
Route::post('/planning/generer', [PlanningController::class, 'generer'])->name('planning.generer');

Route::get('/export', [ExportController::class, 'index'])->name('export.index');
Route::get('/export/download', [ExportController::class, 'download'])->name('export.download');

Route::get('/verification', [VerificationController::class, 'index'])->name('verification.index');