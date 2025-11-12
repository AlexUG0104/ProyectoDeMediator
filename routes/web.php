<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AeropuertoController;

Route::get('/', fn() => redirect()->route('aeropuerto.index'));

Route::get('/aeropuerto',           [AeropuertoController::class, 'index'])->name('aeropuerto.index');
Route::get('/aeropuerto/estado',    [AeropuertoController::class, 'estado'])->name('aeropuerto.estado');
Route::post('/aeropuerto/accion',   [AeropuertoController::class, 'accion'])->name('aeropuerto.accion');
Route::get('/aeropuerto/reiniciar', [AeropuertoController::class, 'reiniciar'])->name('aeropuerto.reiniciar');



