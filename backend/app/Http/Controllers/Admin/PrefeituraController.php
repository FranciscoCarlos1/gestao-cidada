<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prefeitura;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class PrefeituraController extends Controller
{
    /**
     * Listar prefeituras
     */
    public function index(Request $request)
    {
        $query = Prefeitura::with('users');

        // Filtro de busca
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('nome', 'like', "%{$search}%")
                  ->orWhere('cnpj', 'like', "%{$search}%");
        }

        $prefeituras = $query->paginate($request->input('per_page', 10));

        return response()->json($prefeituras);
    }

    /**
     * Obter prefeitura específica
     */
    public function show($id)
    {
        $prefeitura = Prefeitura::with(['users', 'problemas'])->findOrFail($id);

        $stats = [
            'total_users' => $prefeitura->users()->count(),
            'total_problems' => $prefeitura->problemas()->count(),
            'problems_by_status' => $prefeitura->problemas()
                ->groupBy('status')
                ->selectRaw('status, COUNT(*) as count')
                ->get(),
        ];

        return response()->json([
            'prefeitura' => $prefeitura,
            'stats' => $stats,
        ]);
    }

    /**
     * Criar nova prefeitura
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'required|string|unique:prefeituras,cnpj',
            'endereco' => 'nullable|string',
            'telefone' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $prefeitura = Prefeitura::create($validated);

        // Registrar na auditoria
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => 'Prefeitura',
            'model_id' => $prefeitura->id,
            'old_values' => [],
            'new_values' => $prefeitura->toArray(),
        ]);

        return response()->json($prefeitura, 201);
    }

    /**
     * Atualizar prefeitura
     */
    public function update(Request $request, $id)
    {
        $prefeitura = Prefeitura::findOrFail($id);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => "required|string|unique:prefeituras,cnpj,{$id}",
            'endereco' => 'nullable|string',
            'telefone' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $oldValues = $prefeitura->toArray();

        $prefeitura->update($validated);

        // Registrar na auditoria
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'Prefeitura',
            'model_id' => $prefeitura->id,
            'old_values' => $oldValues,
            'new_values' => $prefeitura->fresh()->toArray(),
        ]);

        return response()->json($prefeitura);
    }

    /**
     * Deletar prefeitura
     */
    public function destroy($id)
    {
        $prefeitura = Prefeitura::findOrFail($id);

        // Registrar na auditoria
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => 'Prefeitura',
            'model_id' => $prefeitura->id,
            'old_values' => $prefeitura->toArray(),
            'new_values' => [],
        ]);

        $prefeitura->delete();

        return response()->json(['message' => 'Prefeitura deletada com sucesso'], 200);
    }
}
