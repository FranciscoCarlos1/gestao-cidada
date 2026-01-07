# Sistema SAS - Backend Completo (Phase 1)

## ✅ Implementado

### 1. **Modelos & Migrações**
- ✅ `Role` - roles com permissões (super, admin, cidadao, anonimo)
- ✅ `Permission` - permissões granulares (view, create, update, delete, manage)
- ✅ `TwoFactorAuth` - TOTP + backup codes
- ✅ `AuditLog` - registro de todas as ações
- ✅ `Webhook` - integrações com site de prefeitura
- ✅ `User` - expandido com role_id, 2FA, status, metadata, last_login
- ✅ `Problema` - expandido com status, history, assigned_to, internal_notes

### 2. **Autenticação & Autorização**
- ✅ `AuthController` - register, login com 2FA, logout, anonimo
- ✅ `TwoFactorController` - generate QR, confirm, disable, backup codes
- ✅ `EnsurePermission` middleware - valida permissões
- ✅ `EnsureRole` middleware - valida roles
- ✅ `AuditLogging` middleware - registra todas as ações

### 3. **Endpoints Admin**
- ✅ `UserController` - CRUD users, reset password, toggle status
- ✅ `RoleController` - CRUD roles, gerenciar permissões
- ✅ `PrefeituraController` - CRUD prefeituras, webhooks
- ✅ Proteção via middleware (super admin para users/roles, admin para prefeitura)

### 4. **Fluxo de Problemas**
- ✅ Criar problema (anônimo ou cidadão)
- ✅ Visualizar públicos (anônimo)
- ✅ Meus problemas (cidadão)
- ✅ Atualizar status (admin/prefeitura)
- ✅ Histórico de status
- ✅ Atribui a servidor específico

### 5. **Segurança & Auditoria**
- ✅ 2FA obrigatório para admins (opcional para cidadãos)
- ✅ Tokens Sanctum com revogação de antigos
- ✅ Audit log automático
- ✅ Status de usuário (active, suspended, inactive)

### 6. **Seeders**
- ✅ RolePermissionSeeder - 5 roles + 20 permissões
- ✅ DatabaseSeeder - super, admin, cidadao de demo

## 🔧 Rotas API

```
POST   /api/auth/register           - Registrar
POST   /api/auth/login              - Login (com 2FA)
POST   /api/auth/anonimo            - Sessão anônima
POST   /api/auth/logout             - Logout

POST   /api/2fa/generate            - Gerar QR code
POST   /api/2fa/confirm             - Confirmar 2FA
POST   /api/2fa/disable             - Desativar 2FA

GET    /api/prefeituras             - Listar públicas
GET    /api/problemas               - Listar problemas públicos
POST   /api/problemas               - Criar (anônimo OK)
GET    /api/problemas/{id}          - Detalhes
GET    /api/problemas/mine          - Meus problemas (auth)

Admin Routes (middleware auth:sanctum + role:super/admin):
GET    /api/admin/users             - Listar usuários
POST   /api/admin/users             - Criar usuário
GET    /api/admin/roles             - Listar roles
POST   /api/admin/roles             - Criar role
GET    /api/admin/prefeituras       - Listar prefeituras
POST   /api/admin/prefeituras       - Criar prefeitura
POST   /api/admin/prefeituras/{id}/webhooks - Criar webhook
```

## 📦 Dependências Adicionadas

```
pragmarx/google2fa  ^9.0  - TOTP generation
bacon/bacon-qr-code ^3.0  - QR code rendering
```

## 🚀 Próximos Passos (Phases 2-4)

### Phase 2: Web Dashboard
- Login com 2FA
- Dashboard cidadão (meus problemas)
- Dashboard admin (gerenciar usuários, prefeituras, problemas)
- Anônimo (visualizar públicos)

### Phase 3: Android App
- Integração com toda a API
- Login/Register com 2FA
- Anônimo
- Cidadão (criar, acompanhar)
- Prefeitura (gerenciar)

### Phase 4: Integrações
- Webhooks para site de prefeitura
- Testes automatizados
- CI/CD para backend

## ⚠️ Configuração Local

Para rodar **sem Docker**:
```bash
cd backend
cp .env.example .env
composer install
# Editar .env para SQLite ou PostgreSQL local
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve --host=0.0.0.0 --port=8000
```

Com **Docker** (quando disponível):
```bash
docker compose up -d --build
docker compose exec app php artisan migrate:fresh --seed
```

## 📝 Demo Credentials

```
Super Admin:
  Email: super@demo.test
  Pass:  password
  Role:  super (acesso total)

Admin Prefeitura:
  Email: admin@demo.test
  Pass:  password
  Role:  admin (gerencia sua prefeitura)

Cidadão:
  Email: cidadao@demo.test
  Pass:  password
  Role:  cidadao (cria/acompanha problemas)
```

## 🔐 2FA Setup

1. Chamar `POST /api/2fa/generate`
2. Usuário escaneia QR code com Google Authenticator/Authy
3. Chamar `POST /api/2fa/confirm` com código TOTP
4. Sistema retorna 10 backup codes

No próximo login, enviar `totp_code` junto com email/password.
