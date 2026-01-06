<?php

namespace App\Http\Controllers;

use App\Models\Problema;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ProblemaController extends Controller
{
    /**
     * Registro público (permite anônimo).
     * Se vier Bearer token válido, associa ao usuário autenticado.
     */
    public function store(Request $request)
    {
        $user = $this->tryResolveUserFromBearerToken($request);

        $data = $request->validate([
            'titulo' => ['required','string','max:255'],
            'descricao' => ['required','string'],
            'bairro' => ['required','string','max:255'],
            'rua' => ['nullable','string','max:255'],
            'numero' => ['nullable','string','max:64'],
            'complemento' => ['nullable','string','max:255'],
            'cep' => ['nullable','string','max:32'],
            'cidade' => ['nullable','string','max:255'],
            'uf' => ['nullable','string','size:2'],
            'latitude' => ['nullable','numeric','between:-90,90'],
            'longitude' => ['nullable','numeric','between:-180,180'],
            'prefeitura_id' => ['required','integer','exists:prefeituras,id'],
        ]);

        $data['user_id'] = $user?->id;

        $problema = Problema::create($data);

        return response()->json($problema, 201);
    }

    public function mine(Request $request)
    {
        $user = $request->user();

        return Problema::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();
    }

    private function tryResolveUserFromBearerToken(Request $request)
    {
        $auth = $request->header('Authorization');
        if (!$auth || !str_starts_with($auth, 'Bearer ')) {
            return null;
        }

        $token = trim(substr($auth, 7));
        if ($token === '') return null;

        $access = PersonalAccessToken::findToken($token);
        return $access?->tokenable;
    }
}
