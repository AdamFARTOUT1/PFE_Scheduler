<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;
Route::get('/import', [ImportController::class, 'index'])->name('import.index');
Route::post('/import', [ImportController::class, 'store'])->name('import.store');
Route::get('/', function () {
    return view('welcome');
});
