<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Role::with('permissions')->get(),
        ]);
    }

    public function show(Role $role)
    {
        return response()->json($role->load('permissions', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'unique:roles,name'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        if ($data['permissions'] ?? null) {
            $role->permissions()->attach($data['permissions']);
        }

        return response()->json($role, 201);
    }

    public function update(Request $request, Role $role)
    {
        // Não permitir edição de roles padrão
        if (in_array($role->name, ['super', 'admin', 'cidadao', 'anonimo'])) {
            return response()->json(['message' => 'Não é possível editar roles padrão'], 422);
        }

        $data = $request->validate([
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->update(['description' => $data['description'] ?? $role->description]);

        if (isset($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        return response()->json($role);
    }

    public function destroy(Role $role)
    {
        // Não permitir deleção de roles padrão
        if (in_array($role->name, ['super', 'admin', 'cidadao', 'anonimo'])) {
            return response()->json(['message' => 'Não é possível deletar roles padrão'], 422);
        }

        $role->delete();
        return response()->json(['message' => 'Role deletada']);
    }

    /**
     * Listar todas as permissões
     */
    public function permissions()
    {
        return response()->json([
            'data' => Permission::all(),
        ]);
    }

    /**
     * Adicionar permissão a uma role
     */
    public function grantPermission(Request $request, Role $role)
    {
        $data = $request->validate([
            'permission_id' => ['required', 'integer', 'exists:permissions,id'],
        ]);

        $role->permissions()->attach($data['permission_id']);

        return response()->json(['message' => 'Permissão adicionada']);
    }

    /**
     * Remover permissão de uma role
     */
    public function revokePermission(Request $request, Role $role)
    {
        $data = $request->validate([
            'permission_id' => ['required', 'integer', 'exists:permissions,id'],
        ]);

        $role->permissions()->detach($data['permission_id']);

        return response()->json(['message' => 'Permissão removida']);
    }
}
