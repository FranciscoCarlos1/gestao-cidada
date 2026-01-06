<?php

namespace App\Http\Controllers;

use App\Models\Prefeitura;

class PrefeituraController extends Controller
{
    public function index()
    {
        return Prefeitura::query()
            ->orderBy('nome')
            ->get();
    }
}
