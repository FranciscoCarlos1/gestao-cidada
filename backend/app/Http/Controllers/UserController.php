<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role', 'prefeitura');

        // Filtros
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
        }

        if ($request->has('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('prefeitura_id')) {
            $query->where('prefeitura_id', $request->input('prefeitura_id'));
        }

        return response()->json([
            'data' => $query->paginate($request->input('per_page', 15)),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'prefeitura_id' => ['nullable', 'integer', 'exists:prefeituras,id'],
            'status' => ['required', 'in:active,suspended,inactive'],
        ]);

        $role = Role::where('name', $data['role'])->first();

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'role_id' => $data['role_id'] ?? $role->id,
            'prefeitura_id' => $data['prefeitura_id'],
            'status' => $data['status'],
        ]);

        return response()->json($user, 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load('role', 'prefeitura', 'twoFactorAuth'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email,' . $user->id],
            'role' => ['nullable', 'string'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'prefeitura_id' => ['nullable', 'integer', 'exists:prefeituras,id'],
            'status' => ['nullable', 'in:active,suspended,inactive'],
        ]);

        $user->update($data);

        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'Usuário deletado']);
    }

    /**
     * Resetar password de um usuário
     */
    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user->update(['password' => Hash::make($data['password'])]);

        return response()->json(['message' => 'Senha resetada']);
    }

    /**
     * Suspender/Ativar usuário
     */
    public function toggleStatus(Request $request, User $user)
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,suspended,inactive'],
        ]);

        $user->update(['status' => $data['status']]);

        return response()->json(['message' => "Usuário $data[status]"]);
    }
}
