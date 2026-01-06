<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeocodeController extends Controller
{
    /**
     * Reverse geocoding via Nominatim (OpenStreetMap).
     * Retorna campos úteis: bairro, rua, cidade, uf, cep, etc.
     */
    public function reverse(Request $request)
    {
        $data = $request->validate([
            'lat' => ['required','numeric','between:-90,90'],
            'lng' => ['required','numeric','between:-180,180'],
        ]);

        $lat = $data['lat'];
        $lng = $data['lng'];

        $resp = Http::withHeaders([
                // Nominatim exige User-Agent identificável
                'User-Agent' => 'GestaoCidada/1.0 (contato: suporte@localhost)',
                'Accept-Language' => 'pt-BR,pt;q=0.9',
            ])
            ->timeout(10)
            ->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'jsonv2',
                'lat' => $lat,
                'lon' => $lng,
                'addressdetails' => 1,
            ]);

        if (!$resp->successful()) {
            return response()->json([
                'message' => 'Falha ao consultar geocodificação',
            ], 502);
        }

        $json = $resp->json();
        $addr = $json['address'] ?? [];

        $bairro = $addr['suburb'] ?? $addr['neighbourhood'] ?? $addr['city_district'] ?? $addr['district'] ?? null;
        $rua = $addr['road'] ?? $addr['pedestrian'] ?? $addr['path'] ?? null;
        $numero = $addr['house_number'] ?? null;
        $cep = $addr['postcode'] ?? null;
        $cidade = $addr['city'] ?? $addr['town'] ?? $addr['municipality'] ?? $addr['village'] ?? null;
        $uf = $addr['state_code'] ?? null; // nem sempre vem

        // tenta UF via "state" (ex: Santa Catarina) -> não mapeia automaticamente para sigla
        return response()->json([
            'bairro' => $bairro,
            'rua' => $rua,
            'numero' => $numero,
            'cep' => $cep,
            'cidade' => $cidade,
            'uf' => $uf,
            'display_name' => $json['display_name'] ?? null,
            'raw' => $addr,
        ]);
    }
}
