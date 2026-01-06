<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PrefeituraController;
use App\Http\Controllers\ProblemaController;
use App\Http\Controllers\AdminProblemaController;
use App\Http\Controllers\GeocodeController;
use App\Http\Controllers\CepController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::get('/prefeituras', [PrefeituraController::class, 'index']);

// Registro público (anônimo) - se enviar Bearer token, associa ao usuário
Route::post('/problemas', [ProblemaController::class, 'store']);

// Meus problemas (precisa autenticar)
Route::get('/problemas/mine', [ProblemaController::class, 'mine'])->middleware('auth:sanctum');

// Reverse geocoding
Route::get('/geocode/reverse', [GeocodeController::class, 'reverse']);

// CEP (ViaCEP)
Route::get('/cep/{cep}', [CepController::class, 'show']);

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/problemas', [AdminProblemaController::class, 'index']);
    Route::patch('/problemas/{id}/status', [AdminProblemaController::class, 'updateStatus']);
});
