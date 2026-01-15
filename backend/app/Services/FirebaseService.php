<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Exception;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        try {
            $serviceAccountPath = config('services.firebase.credentials');

            if (!$serviceAccountPath || !file_exists($serviceAccountPath)) {
                throw new Exception('Firebase service account file not found');
            }

            $firebase = (new Factory)
                ->withServiceAccount($serviceAccountPath);

            $this->messaging = $firebase->createMessaging();
        } catch (Exception $e) {
            \Log::error('Firebase initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Envia notificação para um token FCM específico
     *
     * @param string $token Token FCM do dispositivo
     * @param string $title Título da notificação
     * @param string $body Corpo da notificação
     * @param array $data Dados adicionais (opcional)
     * @return bool
     */
    public function sendNotification(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification);

            if (!empty($data)) {
                $message = $message->withData($data);
            }

            $this->messaging->send($message);

            return true;
        } catch (Exception $e) {
            \Log::error('Firebase notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia notificação para múltiplos tokens
     *
     * @param array $tokens Array de tokens FCM
     * @param string $title Título da notificação
     * @param string $body Corpo da notificação
     * @param array $data Dados adicionais (opcional)
     * @return array Array com sucessos e falhas
     */
    public function sendMultipleNotifications(array $tokens, string $title, string $body, array $data = []): array
    {
        $results = [
            'success' => 0,
            'failure' => 0,
            'failed_tokens' => []
        ];

        foreach ($tokens as $token) {
            if ($this->sendNotification($token, $title, $body, $data)) {
                $results['success']++;
            } else {
                $results['failure']++;
                $results['failed_tokens'][] = $token;
            }
        }

        return $results;
    }

    /**
     * Envia notificação de atualização de status do problema
     *
     * @param string $token Token FCM do usuário
     * @param int $problemaId ID do problema
     * @param string $newStatus Novo status do problema
     * @return bool
     */
    public function sendProblemaStatusUpdate(string $token, int $problemaId, string $newStatus): bool
    {
        $statusMessages = [
            'pendente' => 'Seu problema foi registrado e está aguardando análise',
            'em_analise' => 'Seu problema está sendo analisado pela equipe',
            'em_andamento' => 'Sua solicitação está em andamento!',
            'resolvido' => 'Seu problema foi resolvido! ✅',
            'rejeitado' => 'Sua solicitação foi rejeitada'
        ];

        $title = 'Status Atualizado';
        $body = $statusMessages[$newStatus] ?? 'Status do seu problema foi atualizado';

        $data = [
            'problema_id' => (string) $problemaId,
            'status' => $newStatus,
            'type' => 'status_update'
        ];

        return $this->sendNotification($token, $title, $body, $data);
    }

    /**
     * Envia notificação de novo comentário no problema
     *
     * @param string $token Token FCM do usuário
     * @param int $problemaId ID do problema
     * @param string $comentario Texto do comentário
     * @return bool
     */
    public function sendNovoComentario(string $token, int $problemaId, string $comentario): bool
    {
        $title = 'Nova Mensagem';
        $body = substr($comentario, 0, 100) . (strlen($comentario) > 100 ? '...' : '');

        $data = [
            'problema_id' => (string) $problemaId,
            'type' => 'new_comment'
        ];

        return $this->sendNotification($token, $title, $body, $data);
    }

    /**
     * Valida se um token FCM é válido
     *
     * @param string $token Token para validar
     * @return bool
     */
    public function validateToken(string $token): bool
    {
        try {
            // Tenta enviar uma mensagem de validação (dry run)
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create('Validação', 'Token válido'));

            $this->messaging->validate($message);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
