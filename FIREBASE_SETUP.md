# Configuração do Firebase Cloud Messaging (Push Notifications)

Este guia explica como configurar o Firebase Cloud Messaging para receber notificações push no app.

## 📋 Pré-requisitos

- Conta no [Firebase Console](https://console.firebase.google.com/)
- Android Studio instalado
- Projeto já criado no Firebase

## 🚀 Passos para Configuração

### 1. Criar Projeto no Firebase Console

1. Acesse https://console.firebase.google.com/
2. Clique em "Adicionar projeto"
3. Nome do projeto: `gestao-cidada`
4. Siga os passos até concluir

### 2. Adicionar App Android ao Projeto

1. No Firebase Console, clique em "Adicionar app" → Android
2. Preencha:
   - **Package name**: `com.scs.gestaocidada`
   - **App nickname** (opcional): Gestão Cidadã
   - **SHA-1** (opcional): Para autenticação futura
3. Clique em "Registrar app"

### 3. Baixar google-services.json

1. Após registrar o app, baixe o arquivo `google-services.json`
2. Coloque o arquivo em: `android/app/google-services.json`
3. **IMPORTANTE**: Nunca commite este arquivo no git (já está no .gitignore)

### 4. Ativar Cloud Messaging

1. No Firebase Console, vá em "Build" → "Cloud Messaging"
2. Ative o serviço
3. Anote o **Server Key** (será usado no backend)

## 📱 Testando Notificações

### Enviar notificação de teste:

1. Firebase Console → Cloud Messaging
2. Clique em "Enviar sua primeira mensagem"
3. Preencha:
   - **Título**: Teste
   - **Texto**: Notificação de teste
4. Clique em "Testar no dispositivo"
5. Cole o FCM Token (visível nos logs do app)
6. Envie!

### Ver logs do FCM Token:

No código, o token é salvo automaticamente no DataStore. Para ver no logcat:

```kotlin
// Adicione temporariamente no MainActivity
Log.d("FCM_TOKEN", "Token: ${preferencesManager.fcmTokenFlow.value}")
```

## 🔧 Integração com Backend (Laravel)

### 1. Instalar pacote PHP Firebase:

```bash
cd backend
composer require kreait/firebase-php
```

### 2. Adicionar Server Key no .env:

```env
FIREBASE_SERVER_KEY=sua_chave_aqui
```

### 3. Criar serviço de notificações:

```php
// app/Services/FirebaseNotificationService.php
namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseNotificationService
{
    private $messaging;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path('firebase-credentials.json'));
        
        $this->messaging = $factory->createMessaging();
    }

    public function sendToDevice(string $deviceToken, string $title, string $body, array $data = [])
    {
        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification([
                'title' => $title,
                'body' => $body,
            ])
            ->withData($data);

        return $this->messaging->send($message);
    }

    public function sendToMultipleDevices(array $deviceTokens, string $title, string $body)
    {
        foreach ($deviceTokens as $token) {
            $this->sendToDevice($token, $title, $body);
        }
    }
}
```

### 4. Usar no controller:

```php
use App\Services\FirebaseNotificationService;

class ProblemaController extends Controller
{
    public function updateStatus(Request $request, $id)
    {
        $problema = Problema::findOrFail($id);
        $problema->status = $request->status;
        $problema->save();

        // Enviar notificação para o cidadão
        if ($problema->user && $problema->user->fcm_token) {
            $firebaseService = new FirebaseNotificationService();
            $firebaseService->sendToDevice(
                $problema->user->fcm_token,
                'Status Atualizado',
                "Seu problema #{$problema->id} foi atualizado para: {$problema->status}"
            );
        }

        return response()->json($problema);
    }
}
```

### 5. Adicionar campo fcm_token na tabela users:

```bash
php artisan make:migration add_fcm_token_to_users_table
```

```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('fcm_token')->nullable();
    });
}
```

### 6. Endpoint para salvar token:

```php
// routes/api.php
Route::post('/fcm/token', [UserController::class, 'updateFcmToken'])->middleware('auth:sanctum');

// UserController.php
public function updateFcmToken(Request $request)
{
    $request->validate([
        'token' => 'required|string'
    ]);

    $user = $request->user();
    $user->fcm_token = $request->token;
    $user->save();

    return response()->json(['message' => 'Token atualizado']);
}
```

## 📝 Chamada no Android (ApiService)

Adicione ao `ApiService.kt`:

```kotlin
@POST("api/fcm/token")
suspend fun updateFcmToken(
    @Body request: FcmTokenRequest,
    @Header("Authorization") auth: String
): Response<Unit>
```

E em `ApiModels.kt`:

```kotlin
data class FcmTokenRequest(
    val token: String
)
```

## 🔔 Tipos de Notificações

### 1. Notificação simples:
```json
{
  "notification": {
    "title": "Título",
    "body": "Mensagem"
  }
}
```

### 2. Notificação com dados customizados:
```json
{
  "notification": {
    "title": "Status Atualizado",
    "body": "Problema resolvido"
  },
  "data": {
    "problema_id": "123",
    "action": "view_problem"
  }
}
```

## 🎯 Boas Práticas

1. **Sempre peça permissão** antes de enviar notificações
2. **Respeite as preferências** do usuário (toggle on/off)
3. **Não envie spam** - notifique apenas eventos importantes
4. **Use data payload** para ações customizadas
5. **Teste em diferentes dispositivos** (Android versions)

## 🐛 Troubleshooting

### Token não aparece:
- Verifique se google-services.json está correto
- Rebuild do projeto
- Limpe cache: `./gradlew clean`

### Notificações não chegam:
- Verifique permissões no AndroidManifest
- Confirme que o serviço está registrado
- Veja logs do Firebase Console

### Erro de autenticação:
- Verifique Server Key no backend
- Confirme que o app está registrado no Firebase

## 📚 Referências

- [Firebase Cloud Messaging Docs](https://firebase.google.com/docs/cloud-messaging)
- [Android Notifications Guide](https://developer.android.com/develop/ui/views/notifications)
- [Kreait Firebase PHP](https://firebase-php.readthedocs.io/)
