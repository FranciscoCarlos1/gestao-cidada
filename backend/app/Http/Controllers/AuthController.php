<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\TwoFactorAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        // Obter role de cidadão padrão
        $cidadaoRole = Role::where('name', 'cidadao')->first();

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'role' => 'cidadao',
            'role_id' => $cidadaoRole->id,
            'status' => 'active',
        ]);

        // Criar registro 2FA (desativado por padrão)
        TwoFactorAuth::create([
            'user_id' => $user->id,
            'secret' => $this->google2fa->generateSecretKey(),
            'enabled' => false,
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Usuário registrado com sucesso',
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'totp_code' => ['nullable', 'string', 'size:6'],
        ]);

        $user = User::where('email', strtolower($data['email']))->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        // Verificar status do usuário
        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Usuário inativo ou suspenso.'],
            ]);
        }

        // Verificar 2FA se habilitado
        if ($user->two_factor_enabled) {
            $twoFactor = $user->twoFactorAuth;

            if (!$data['totp_code']) {
                return response()->json([
                    'message' => 'Código 2FA requerido',
                    'requires_2fa' => true,
                ], 422);
            }

            if (!$this->google2fa->verifyKey($twoFactor->secret, $data['totp_code'])) {
                throw ValidationException::withMessages([
                    'totp_code' => ['Código 2FA inválido.'],
                ]);
            }
        }

        // Revogar tokens antigos
        $user->tokens()->delete();

        // Atualizar last_login_at
        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'user' => $user->load('role'),
            'token' => $token,
            'requires_2fa' => false,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()?->delete();
        }

        return response()->json(['message' => 'Deslogado com sucesso']);
    }

    public function anonimo(Request $request)
    {
        // Retorna um token anônimo sem persistir usuário
        $tokenData = base64_encode(json_encode([
            'type' => 'anonimo',
            'session_id' => bin2hex(random_bytes(16)),
            'created_at' => now()->toIso8601String(),
        ]));

        return response()->json([
            'token' => $tokenData,
            'user' => null,
            'type' => 'anonimo',
        ]);
    }
}
