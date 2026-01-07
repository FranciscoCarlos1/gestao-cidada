<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use App\Models\User;
use App\Models\Problema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Salva o token FCM do usuário
     *
     * POST /api/fcm/token
     * Body: { "fcm_token": "..." }
     */
    public function saveFcmToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Token FCM inválido',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $user = Auth::user();
            $user->fcm_token = $request->fcm_token;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Token FCM salvo com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar token FCM',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove o token FCM do usuário (logout/desabilitar notificações)
     *
     * DELETE /api/fcm/token
     */
    public function removeFcmToken(Request $request)
    {
        try {
            $user = Auth::user();
            $user->fcm_token = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Token FCM removido com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover token FCM',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envia notificação de teste para o usuário autenticado
     *
     * POST /api/notifications/test
     */
    public function sendTestNotification(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user->fcm_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não possui token FCM registrado'
                ], 400);
            }

            $success = $this->firebaseService->sendNotification(
                $user->fcm_token,
                '🔔 Notificação de Teste',
                'Se você recebeu esta mensagem, as notificações estão funcionando!',
                ['type' => 'test']
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notificação de teste enviada com sucesso'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Falha ao enviar notificação de teste'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar notificação',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envia notificação sobre atualização de status do problema
     * (Apenas admin/prefeitura)
     *
     * POST /api/problemas/{id}/notify
     * Body: { "title": "...", "message": "...", "data": {...} }
     */
    public function notifyProblemaUpdate(Request $request, $id)
    {
        $user = Auth::user();

        // Verificar se usuário tem permissão (apenas admin e prefeitura)
        if (!in_array($user->role, ['super', 'admin', 'prefeitura'])) {
            return response()->json([
                'success' => false,
                'message' => 'Sem permissão para enviar notificações'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $problema = Problema::findOrFail($id);
            $cidadao = User::find($problema->user_id);

            if (!$cidadao || !$cidadao->fcm_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cidadão não possui token FCM registrado'
                ], 400);
            }

            $data = $request->data ?? [];
            $data['problema_id'] = (string) $id;
            $data['type'] = $data['type'] ?? 'problema_update';

            $success = $this->firebaseService->sendNotification(
                $cidadao->fcm_token,
                $request->title,
                $request->message,
                $data
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notificação enviada com sucesso',
                    'recipient' => $cidadao->name
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Falha ao enviar notificação'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar notificação',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envia notificação para todos os cidadãos de uma prefeitura
     * (Apenas admin/prefeitura)
     *
     * POST /api/notifications/broadcast
     * Body: { "title": "...", "message": "...", "prefeitura_id": 1 }
     */
    public function broadcastNotification(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['super', 'admin', 'prefeitura'])) {
            return response()->json([
                'success' => false,
                'message' => 'Sem permissão para enviar notificações'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'prefeitura_id' => 'nullable|exists:prefeituras,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Se prefeitura_id for especificado, pegar apenas usuários dessa prefeitura
            // Caso contrário, enviar para todos os cidadãos
            $query = User::where('role', 'cidadao')
                ->whereNotNull('fcm_token');

            if ($request->has('prefeitura_id')) {
                $query->where('prefeitura_id', $request->prefeitura_id);
            }

            $users = $query->get();
            $tokens = $users->pluck('fcm_token')->toArray();

            if (empty($tokens)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum usuário com token FCM encontrado'
                ], 400);
            }

            $results = $this->firebaseService->sendMultipleNotifications(
                $tokens,
                $request->title,
                $request->message,
                ['type' => 'broadcast']
            );

            return response()->json([
                'success' => true,
                'message' => 'Notificações enviadas',
                'total_users' => count($tokens),
                'sent_successfully' => $results['success'],
                'failed' => $results['failure']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar notificações',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
