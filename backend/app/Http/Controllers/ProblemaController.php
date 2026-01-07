<?php

namespace App\Http\Controllers;

use App\Models\Problema;
use App\Models\AuditLog;
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
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['required', 'string'],
            'bairro' => ['required', 'string', 'max:255'],
            'rua' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:64'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'cep' => ['nullable', 'string', 'max:32'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'uf' => ['nullable', 'string', 'size:2'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'prefeitura_id' => ['required', 'integer', 'exists:prefeituras,id'],
        ]);

        $data['user_id'] = $user?->id;
        $data['status'] = 'aberto';

        $problema = Problema::create($data);

        // Log de auditoria
        AuditLog::log('create', 'Problema', $problema->id, $data);

        return response()->json($problema, 201);
    }

    /**
     * Listar meus problemas (autenticado)
     */
    public function mine(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'data' => Problema::query()
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    /**
     * Listar problemas públicos (anônimo ou autenticado)
     */
    public function index(Request $request)
    {
        $query = Problema::with('prefeitura', 'user', 'assignedTo');

        if ($request->has('prefeitura_id')) {
            $query->where('prefeitura_id', $request->input('prefeitura_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('titulo', 'like', "%$search%")
                ->orWhere('descricao', 'like', "%$search%");
        }

        return response()->json([
            'data' => $query->orderByDesc('created_at')->paginate($request->input('per_page', 15)),
        ]);
    }

    /**
     * Visualizar detalhes de um problema
     */
    public function show(Problema $problema)
    {
        return response()->json($problema->load('prefeitura', 'user', 'assignedTo'));
    }

    /**
     * Atualizar status de um problema (admin/prefeitura)
     */
    public function updateStatus(Request $request, Problema $problema)
    {
        $data = $request->validate([
            'status' => ['required', 'in:aberto,em_andamento,resolvido,fechado,rejeitado'],
            'internal_notes' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $oldStatus = $problema->status;

        if (isset($data['internal_notes'])) {
            $problema->internal_notes = $data['internal_notes'];
        }

        if (isset($data['assigned_to'])) {
            $problema->assigned_to = $data['assigned_to'];
        }

        $problema->updateStatus($data['status']);

        return response()->json($problema);
    }

    private function tryResolveUserFromBearerToken(Request $request)
    {
        $auth = $request->header('Authorization');
        if (!$auth || !str_starts_with($auth, 'Bearer ')) {
            return null;
        }

        $token = trim(substr($auth, 7));
        if ($token === '') return null;

        $accessToken = PersonalAccessToken::findToken($token);
        return $accessToken?->tokenable;
    }
}

        $access = PersonalAccessToken::findToken($token);
        return $access?->tokenable;
    }
}
