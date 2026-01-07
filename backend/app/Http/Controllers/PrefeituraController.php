<?php

namespace App\Http\Controllers;

use App\Models\Prefeitura;
use Illuminate\Http\Request;

class PrefeituraController extends Controller
{
    public function index(Request $request)
    {
        $query = Prefeitura::withCount('users', 'problemas');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('nome', 'like', "%$search%")
                ->orWhere('cidade', 'like', "%$search%");
        }

        return response()->json([
            'data' => $query->paginate($request->input('per_page', 15)),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'unique:prefeituras,slug'],
            'cnpj' => ['nullable', 'string', 'max:14', 'unique:prefeituras,cnpj'],
            'email_contato' => ['nullable', 'email'],
            'telefone' => ['nullable', 'string'],
            'cidade' => ['required', 'string'],
            'uf' => ['required', 'string', 'size:2'],
        ]);

        $prefeitura = Prefeitura::create($data);

        return response()->json($prefeitura, 201);
    }

    public function show(Prefeitura $prefeitura)
    {
        return response()->json($prefeitura->load('users', 'problemas', 'webhooks'));
    }

    public function update(Request $request, Prefeitura $prefeitura)
    {
        $data = $request->validate([
            'nome' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'unique:prefeituras,slug,' . $prefeitura->id],
            'cnpj' => ['nullable', 'string', 'max:14', 'unique:prefeituras,cnpj,' . $prefeitura->id],
            'email_contato' => ['nullable', 'email'],
            'telefone' => ['nullable', 'string'],
            'cidade' => ['nullable', 'string'],
            'uf' => ['nullable', 'string', 'size:2'],
        ]);

        $prefeitura->update($data);

        return response()->json($prefeitura);
    }

    public function destroy(Prefeitura $prefeitura)
    {
        $prefeitura->delete();
        return response()->json(['message' => 'Prefeitura deletada']);
    }

    /**
     * Listar webhooks de uma prefeitura
     */
    public function webhooks(Prefeitura $prefeitura)
    {
        return response()->json([
            'data' => $prefeitura->webhooks,
        ]);
    }

    /**
     * Criar webhook para uma prefeitura
     */
    public function createWebhook(Request $request, Prefeitura $prefeitura)
    {
        $data = $request->validate([
            'event' => ['required', 'string'],
            'url' => ['required', 'url'],
            'secret' => ['nullable', 'string'],
        ]);

        $webhook = $prefeitura->webhooks()->create($data);

        return response()->json($webhook, 201);
    }

    /**
     * Deletar webhook
     */
    public function deleteWebhook(Prefeitura $prefeitura, int $webhookId)
    {
        $webhook = $prefeitura->webhooks()->findOrFail($webhookId);
        $webhook->delete();

        return response()->json(['message' => 'Webhook deletado']);
    }
}
