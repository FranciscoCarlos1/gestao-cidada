<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CepController extends Controller
{
    /**
     * Consulta CEP via ViaCEP.
     *
     * GET /api/cep/{cep}
     */
    public function show(string $cep)
    {
        // Mantém apenas dígitos
        $cep = preg_replace('/\D/', '', $cep ?? '');

        if (strlen($cep) !== 8) {
            return response()->json([
                'message' => 'CEP inválido. Informe 8 dígitos.'
            ], 422);
        }

        $url = "https://viacep.com.br/ws/{$cep}/json/";

        $resp = Http::timeout(10)->acceptJson()->get($url);

        if (!$resp->ok()) {
            return response()->json([
                'message' => 'Falha ao consultar ViaCEP.'
            ], 502);
        }

        $data = $resp->json();

        if (isset($data['erro']) && $data['erro'] === true) {
            return response()->json([
                'message' => 'CEP não encontrado.'
            ], 404);
        }

        return response()->json($data);
    }
}
