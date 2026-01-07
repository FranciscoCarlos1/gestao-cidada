<?php

use Illuminate\Support\Facades\Route;

// Dashboard SPA (single page app)
Route::get('/', function () {
    return view('app');
});

Route::get('/dashboard', function () {
    return view('app');
});

// Fallback para rotas não encontradas (serve app.blade.php para SPAs)
Route::fallback(function () {
    return view('app');
});
