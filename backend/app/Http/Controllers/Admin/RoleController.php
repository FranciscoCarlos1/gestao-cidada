<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Listar todos os roles
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    /**
     * Obter role específica
     */
    public function show($id)
    {
        $role = Role::with(['permissions', 'users'])->findOrFail($id);
        return response()->json($role);
    }

    /**
     * Criar novo role
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'description' => 'nullable|string',
            'permissions' => 'array|exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Atribuir permissões
        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        // Registrar na auditoria
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => 'Role',
            'model_id' => $role->id,
            'old_values' => [],
            'new_values' => $role->toArray(),
        ]);

        return response()->json($role->load('permissions'), 201);
    }

    /**
     * Atualizar role
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => "required|string|unique:roles,name,{$id}",
            'description' => 'nullable|string',
            'permissions' => 'array|exists:permissions,id',
        ]);

        $oldValues = $role->toArray();

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Atualizar permissões
        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        // Registrar na auditoria
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'Role',
            'model_id' => $role->id,
            'old_values' => $oldValues,
            'new_values' => $role->fresh()->toArray(),
        ]);

        return response()->json($role->load('permissions'));
    }

    /**
     * Deletar role
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Registrar na auditoria
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => 'Role',
            'model_id' => $role->id,
            'old_values' => $role->toArray(),
            'new_values' => [],
        ]);

        $role->delete();

        return response()->json(['message' => 'Role deletada com sucesso'], 200);
    }

    /**
     * Listar todas as permissões
     */
    public function listPermissions()
    {
        $permissions = Permission::all();
        return response()->json($permissions);
    }

    /**
     * Atribuir permissões a uma role
     */
    public function assignPermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'permissions' => 'required|array|exists:permissions,id',
        ]);

        $oldPermissions = $role->permissions->pluck('id')->toArray();

        $role->permissions()->sync($validated['permissions']);

        // Registrar na auditoria
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'Role',
            'model_id' => $role->id,
            'old_values' => ['permissions' => $oldPermissions],
            'new_values' => ['permissions' => $validated['permissions']],
        ]);

        return response()->json($role->load('permissions'));
    }

    /**
     * Obter usuários de uma role
     */
    public function getUsers($id)
    {
        $role = Role::findOrFail($id);
        $users = $role->users()->paginate(10);
        return response()->json($users);
    }
}
