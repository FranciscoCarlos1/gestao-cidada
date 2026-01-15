<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Listar usuários com filtros
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'prefeitura']);

        // Filtros
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        if ($request->has('role_id')) {
            $query->whereHas('roles', fn($q) => $q->where('id', $request->input('role_id')));
        }

        if ($request->has('prefeitura_id')) {
            $query->where('prefeitura_id', $request->input('prefeitura_id'));
        }

        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('status', 'active');
            } elseif ($status === 'inactive') {
                $query->where('status', 'inactive');
            }
        }

        // Paginação
        $perPage = $request->input('per_page', 10);
        $users = $query->paginate($perPage);

        return response()->json($users);
    }

    /**
     * Obter usuário específico
     */
    public function show($id)
    {
        $user = User::with(['roles', 'prefeitura', 'auditLogs'])->findOrFail($id);
        return response()->json($user);
    }

    /**
     * Criar novo usuário
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'prefeitura_id' => 'nullable|exists:prefeituras,id',
            'status' => 'required|in:active,inactive',
            'roles' => 'array|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'prefeitura_id' => $validated['prefeitura_id'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        // Atribuir roles
        if (!empty($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        // Registrar na auditoria
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => 'User',
            'model_id' => $user->id,
            'old_values' => [],
            'new_values' => $user->toArray(),
        ]);

        return response()->json($user->load(['roles', 'prefeitura']), 201);
    }

    /**
     * Atualizar usuário
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($id)],
            'password' => 'nullable|string|min:8|confirmed',
            'prefeitura_id' => 'nullable|exists:prefeituras,id',
            'status' => 'required|in:active,inactive',
            'roles' => 'array|exists:roles,id',
        ]);

        $oldValues = $user->toArray();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'prefeitura_id' => $validated['prefeitura_id'] ?? null,
            'status' => $validated['status'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => bcrypt($validated['password'])]);
        }

        // Atualizar roles
        if (isset($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        // Registrar na auditoria
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'User',
            'model_id' => $user->id,
            'old_values' => $oldValues,
            'new_values' => $user->fresh()->toArray(),
        ]);

        return response()->json($user->load(['roles', 'prefeitura']));
    }

    /**
     * Deletar usuário
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Registrar na auditoria
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => 'User',
            'model_id' => $user->id,
            'old_values' => $user->toArray(),
            'new_values' => [],
        ]);

        $user->delete();

        return response()->json(['message' => 'Usuário deletado com sucesso'], 200);
    }

    /**
     * Alternar status do usuário
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $oldStatus = $user->status;

        $user->update(['status' => $newStatus]);

        // Registrar na auditoria
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'User',
            'model_id' => $user->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $newStatus],
        ]);

        return response()->json($user);
    }
}
