<?php

namespace App\Http\Controllers;

use App\Models\TwoFactorAuth;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Gera novo secret 2FA e QR code para o usuário
     */
    public function generate(Request $request)
    {
        $user = $request->user();
        $twoFactor = $user->twoFactorAuth;

        // Gerar novo secret
        $secret = $this->google2fa->generateSecretKey();

        // Atualizar (sem confirmar ainda)
        $twoFactor->update(['secret' => $secret]);

        // Gerar QR code
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return response()->json([
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
            'message' => 'Escaneie o QR code com seu app autenticador',
        ]);
    }

    /**
     * Confirma ativação de 2FA
     */
    public function confirm(Request $request)
    {
        $data = $request->validate([
            'totp_code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        $twoFactor = $user->twoFactorAuth;

        if (!$this->google2fa->verifyKey($twoFactor->secret, $data['totp_code'])) {
            return response()->json(['message' => 'Código inválido'], 422);
        }

        // Gerar códigos de backup
        $backupCodes = array_map(fn () => bin2hex(random_bytes(4)), range(1, 10));

        $twoFactor->update([
            'enabled' => true,
            'confirmed_at' => now(),
            'backup_codes' => $backupCodes,
        ]);

        $user->update(['two_factor_enabled' => true]);

        return response()->json([
            'message' => '2FA ativado com sucesso',
            'backup_codes' => $backupCodes,
        ]);
    }

    /**
     * Desativa 2FA
     */
    public function disable(Request $request)
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (!Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Senha incorreta'], 422);
        }

        $user->twoFactorAuth->update([
            'enabled' => false,
            'confirmed_at' => null,
        ]);

        $user->update(['two_factor_enabled' => false]);

        return response()->json(['message' => '2FA desativado']);
    }

    /**
     * Valida usando código de backup
     */
    public function verifyBackupCode(Request $request)
    {
        $data = $request->validate([
            'backup_code' => ['required', 'string'],
        ]);

        $user = $request->user();
        $twoFactor = $user->twoFactorAuth;

        if (!$twoFactor->enabled) {
            return response()->json(['message' => '2FA não ativado'], 422);
        }

        $codes = $twoFactor->backup_codes ?? [];
        $codeIndex = array_search($data['backup_code'], $codes);

        if ($codeIndex === false) {
            return response()->json(['message' => 'Código de backup inválido'], 422);
        }

        // Remover código usado
        unset($codes[$codeIndex]);
        $twoFactor->update(['backup_codes' => array_values($codes)]);

        return response()->json(['message' => 'Código de backup válido']);
    }
}
