# 🔔 Integração Backend - Firebase Cloud Messaging

Este documento detalha a implementação completa do Firebase Cloud Messaging (FCM) no backend Laravel.

## 📦 Dependências Instaladas

```bash
composer require kreait/firebase-php
```

**Versão instalada**: `kreait/firebase-php:^7.24`

### Pacotes incluídos:
- `google/auth` - Autenticação Google
- `google/cloud-storage` - Storage do Firebase
- `lcobucci/jwt` - JWT tokens
- `firebase/php-jwt` - Firebase JWT
- E outras dependências necessárias

---

## 🗄️ Estrutura do Banco de Dados

### Migration: `2026_01_07_162521_add_fcm_token_to_users_table.php`

Adiciona o campo `fcm_token` na tabela `users`:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('fcm_token')->nullable()->after('remember_token');
});
```

**Campo**:
- `fcm_token`: Token do dispositivo para receber notificações push
- `nullable`: Usuário pode não ter token (não habilitou notificações)

Para executar:
```bash
php artisan migrate
```

---

## 🔧 Configuração

### 1. Arquivo de Credenciais do Firebase

1. Acesse o [Firebase Console](https://console.firebase.google.com)
2. Selecione seu projeto
3. Vá em **Project Settings** → **Service Accounts**
4. Clique em **Generate New Private Key**
5. Baixe o arquivo JSON

6. Salve o arquivo como:
```
backend/storage/app/firebase-credentials.json
```

### 2. Variável de Ambiente

Adicione ao `.env`:

```env
FIREBASE_CREDENTIALS=storage/app/firebase-credentials.json
```

### 3. Configuração em `config/services.php`

Já adicionado:

```php
'firebase' => [
    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase-credentials.json')),
],
```

---

## 📁 Arquivos Criados/Modificados

### 🆕 Novos Arquivos

#### `app/Services/FirebaseService.php`
Serviço principal para envio de notificações Firebase.

**Métodos**:
- `sendNotification()` - Envia notificação para um token
- `sendMultipleNotifications()` - Envia para múltiplos tokens
- `sendProblemaStatusUpdate()` - Notificação de status de problema
- `sendNovoComentario()` - Notificação de novo comentário
- `validateToken()` - Valida token FCM

**Exemplo de uso**:
```php
use App\Services\FirebaseService;

$firebaseService = app(FirebaseService::class);

$firebaseService->sendNotification(
    token: 'fcm-token-do-usuario',
    title: 'Novo Status',
    body: 'Seu problema foi resolvido!',
    data: ['problema_id' => '123']
);
```

#### `app/Http/Controllers/NotificationController.php`
Controller com endpoints para gerenciar notificações.

**Endpoints**:
1. `POST /api/fcm/token` - Salvar token FCM
2. `DELETE /api/fcm/token` - Remover token FCM
3. `POST /api/notifications/test` - Enviar notificação de teste
4. `POST /api/problemas/{id}/notify` - Notificar sobre problema
5. `POST /api/notifications/broadcast` - Broadcast para cidadãos

---

## 🌐 API Endpoints

### 1. Salvar Token FCM

**Request**:
```http
POST /api/fcm/token
Authorization: Bearer {token}
Content-Type: application/json

{
  "fcm_token": "eXNjEIK7R_6..."
}
```

**Response**:
```json
{
  "success": true,
  "message": "Token FCM salvo com sucesso"
}
```

---

### 2. Remover Token FCM

**Request**:
```http
DELETE /api/fcm/token
Authorization: Bearer {token}
```

**Response**:
```json
{
  "success": true,
  "message": "Token FCM removido com sucesso"
}
```

---

### 3. Enviar Notificação de Teste

**Request**:
```http
POST /api/notifications/test
Authorization: Bearer {token}
```

**Response**:
```json
{
  "success": true,
  "message": "Notificação de teste enviada com sucesso"
}
```

---

### 4. Notificar Sobre Problema (Admin/Prefeitura)

**Request**:
```http
POST /api/problemas/123/notify
Authorization: Bearer {admin-token}
Content-Type: application/json

{
  "title": "Status Atualizado",
  "message": "Seu problema está em andamento!",
  "data": {
    "status": "em_andamento"
  }
}
```

**Response**:
```json
{
  "success": true,
  "message": "Notificação enviada com sucesso",
  "recipient": "João Silva"
}
```

---

### 5. Broadcast para Cidadãos (Admin/Prefeitura)

**Request**:
```http
POST /api/notifications/broadcast
Authorization: Bearer {admin-token}
Content-Type: application/json

{
  "title": "Manutenção Programada",
  "message": "O sistema ficará em manutenção amanhã às 14h",
  "prefeitura_id": 1
}
```

**Response**:
```json
{
  "success": true,
  "message": "Notificações enviadas",
  "total_users": 150,
  "sent_successfully": 147,
  "failed": 3
}
```

---

## 🔐 Permissões

### Endpoints Públicos (Auth obrigatório):
- `POST /api/fcm/token` - Qualquer usuário autenticado
- `DELETE /api/fcm/token` - Qualquer usuário autenticado
- `POST /api/notifications/test` - Qualquer usuário autenticado

### Endpoints Administrativos:
- `POST /api/problemas/{id}/notify` - Roles: `super`, `admin`, `prefeitura`
- `POST /api/notifications/broadcast` - Roles: `super`, `admin`, `prefeitura`

---

## 💡 Exemplos de Uso

### Enviar Notificação quando Problema é Resolvido

No controller de atualização de problema:

```php
use App\Services\FirebaseService;

public function updateStatus(Request $request, $id)
{
    $problema = Problema::findOrFail($id);
    $problema->status = $request->status;
    $problema->save();

    // Enviar notificação ao cidadão
    $cidadao = $problema->user;
    
    if ($cidadao && $cidadao->fcm_token) {
        $firebaseService = app(FirebaseService::class);
        
        $firebaseService->sendProblemaStatusUpdate(
            token: $cidadao->fcm_token,
            problemaId: $problema->id,
            newStatus: $problema->status
        );
    }

    return response()->json(['success' => true]);
}
```

### Notificar Todos os Cidadãos de uma Prefeitura

```php
use App\Services\FirebaseService;
use App\Models\User;

$firebaseService = app(FirebaseService::class);

$tokens = User::where('role', 'cidadao')
    ->where('prefeitura_id', 1)
    ->whereNotNull('fcm_token')
    ->pluck('fcm_token')
    ->toArray();

$results = $firebaseService->sendMultipleNotifications(
    tokens: $tokens,
    title: 'Aviso Importante',
    body: 'Nova funcionalidade disponível no app!',
    data: ['type' => 'feature_announcement']
);

// $results['success'] - quantidade de envios bem-sucedidos
// $results['failure'] - quantidade de falhas
// $results['failed_tokens'] - array com tokens que falharam
```

---

## 🧪 Testando a Integração

### 1. Via API (Postman/Insomnia)

1. **Login** para obter token:
```http
POST /api/auth/login
{
  "email": "cidadao@email.com",
  "password": "senha123"
}
```

2. **Salvar token FCM**:
```http
POST /api/fcm/token
Authorization: Bearer {token}
{
  "fcm_token": "seu-token-fcm-do-android"
}
```

3. **Enviar notificação de teste**:
```http
POST /api/notifications/test
Authorization: Bearer {token}
```

### 2. Via Tinker (CLI)

```bash
php artisan tinker
```

```php
// Importar classes
use App\Services\FirebaseService;
use App\Models\User;

// Criar instância do serviço
$firebase = app(FirebaseService::class);

// Buscar um usuário com token
$user = User::whereNotNull('fcm_token')->first();

// Enviar notificação
$firebase->sendNotification(
    $user->fcm_token,
    'Teste via Tinker',
    'Esta é uma notificação de teste!',
    ['test' => 'true']
);
```

---

## 🚨 Troubleshooting

### Erro: "Firebase service account file not found"

**Causa**: Arquivo de credenciais não encontrado.

**Solução**:
1. Verifique se o arquivo existe em `backend/storage/app/firebase-credentials.json`
2. Confirme a variável `.env`: `FIREBASE_CREDENTIALS=storage/app/firebase-credentials.json`
3. Execute: `php artisan config:clear`

---

### Erro: "Token inválido"

**Causa**: Token FCM expirado ou inválido.

**Solução**:
1. Regenere o token no app Android
2. Salve novamente via `POST /api/fcm/token`
3. Os tokens FCM expiram periodicamente

---

### Notificações não chegam no dispositivo

**Checklist**:
- ✅ Token FCM foi salvo corretamente no banco?
- ✅ Credenciais Firebase estão corretas?
- ✅ App Android tem `google-services.json`?
- ✅ Dispositivo está conectado à internet?
- ✅ Notificações estão habilitadas no Android?
- ✅ Logs do Laravel mostram erros? (`storage/logs/laravel.log`)

---

### Ver logs de erros:

```bash
tail -f storage/logs/laravel.log
```

---

## 📊 Estrutura de Dados das Notificações

### Notificação Básica
```json
{
  "title": "Título da notificação",
  "body": "Corpo da mensagem",
  "data": {
    "type": "tipo_notificacao",
    "custom_key": "custom_value"
  }
}
```

### Notificação de Status de Problema
```json
{
  "title": "Status Atualizado",
  "body": "Seu problema foi resolvido! ✅",
  "data": {
    "problema_id": "123",
    "status": "resolvido",
    "type": "status_update"
  }
}
```

### Notificação de Novo Comentário
```json
{
  "title": "Nova Mensagem",
  "body": "A prefeitura respondeu seu problema",
  "data": {
    "problema_id": "123",
    "type": "new_comment"
  }
}
```

---

## 🔒 Segurança

### Credenciais Firebase
- ❌ **NUNCA** commitar o arquivo `firebase-credentials.json`
- ✅ Arquivo já está no `.gitignore`
- ✅ Use arquivo `.example` como template
- ✅ Em produção, use variáveis de ambiente seguras

### Tokens FCM
- ✅ Campo `fcm_token` está no `$hidden` do model User
- ✅ Tokens não são retornados em respostas JSON
- ✅ Apenas o próprio usuário pode atualizar seu token

### Permissões
- ✅ Apenas admin/prefeitura podem enviar notificações broadcast
- ✅ Middleware `role:super,admin,prefeitura` protege endpoints

---

## 📈 Melhorias Futuras

- [ ] Queue/Jobs para envio assíncrono de notificações em massa
- [ ] Histórico de notificações enviadas
- [ ] Templates de notificações reutilizáveis
- [ ] Agendamento de notificações
- [ ] Notificações rich (imagens, ações)
- [ ] Analytics de taxa de abertura
- [ ] Segmentação avançada de usuários

---

## 📚 Referências

- [Kreait Firebase PHP](https://firebase-php.readthedocs.io/)
- [Firebase Cloud Messaging](https://firebase.google.com/docs/cloud-messaging)
- [Laravel Notifications](https://laravel.com/docs/notifications)
- [Google Service Accounts](https://cloud.google.com/iam/docs/service-accounts)

---

## 👨‍💻 Implementado por

**FRANCISCO CARLOS DE SOUSA**  
**Cargo**: Técnico de TI - Instituto Federal Catarinense  
**Data**: 7 de Janeiro de 2026

---

## 📝 Resumo

✅ **Instalado**: `kreait/firebase-php:^7.24`  
✅ **Migration**: Campo `fcm_token` na tabela `users`  
✅ **Service**: `FirebaseService` com métodos completos  
✅ **Controller**: `NotificationController` com 5 endpoints  
✅ **Rotas**: Endpoints documentados e protegidos  
✅ **Config**: `services.firebase` configurado  
✅ **Segurança**: `.gitignore` atualizado  
✅ **Documentação**: README completo

**Próximo passo**: Configure o arquivo `firebase-credentials.json` e comece a enviar notificações! 🚀
