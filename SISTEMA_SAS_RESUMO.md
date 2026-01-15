# SISTEMA SAS - GESTÃO CIDADÃ
## Resumo Completo: Fases 1-2 ✅

---

## 🎯 Visão Geral

Um **sistema SAS (Software as a Service) completo** de gestão de problemas urbanos, permitindo que cidadãos reportem issues, prefeituras gerenciem resoluções, com múltiplos níveis de acesso, segurança 2FA e integrações.

**Tech Stack:**
- Backend: Laravel 11 + PostgreSQL 16 + Sanctum
- Frontend: Blade + Alpine.js + Tailwind CSS v4
- Mobile: Kotlin + Jetpack Compose (Phase 3)
- Infraestrutura: Docker Compose (optional), PHP-FPM + Nginx

---

## ✅ Phase 1: Backend Completo

### Modelos & Bancos de Dados
- ✅ **Role** (super, admin, cidadao, anonimo) + permissões granulares
- ✅ **Permission** (20+ permissões para controle granular)
- ✅ **User** (expandido com 2FA, status, role_id, metadata, last_login)
- ✅ **TwoFactorAuth** (TOTP + 10 backup codes)
- ✅ **Problema** (status, histórico, assigned_to, notas internas)
- ✅ **AuditLog** (rastreamento de todas as ações)
- ✅ **Webhook** (para integrações com site de prefeitura)

### Autenticação & Autorização
- ✅ Login com email/password
- ✅ 2FA obrigatório (TOTP via Google Authenticator)
- ✅ Registro automático
- ✅ Login anônimo (sessão sem usuário)
- ✅ Middleware de roles e permissões
- ✅ Bearer tokens (Sanctum)
- ✅ Revogação de tokens antigos

### Controllers & Endpoints
- ✅ **AuthController**: register, login (com 2FA), logout, anonimo
- ✅ **TwoFactorController**: gerar QR, confirmar, desativar, backup codes
- ✅ **UserController**: CRUD, suspender, resetar senha
- ✅ **RoleController**: CRUD roles, sincronizar permissões
- ✅ **PrefeituraController**: CRUD prefeituras, webhooks
- ✅ **ProblemaController**: criar, listar (público/autenticado), atualizar status
- ✅ **AuditLogController**: filtrar logs de auditoria
- ✅ **AdminProblemaController**: gerenciar problemas da prefeitura

### Seeders
- ✅ RolePermissionSeeder (5 roles + 20 permissões)
- ✅ DatabaseSeeder (super, admin, cidadao de demo)

### Segurança
- ✅ Criptografia de senhas (bcrypt)
- ✅ 2FA (TOTP - Time-based One-Time Password)
- ✅ Audit logging automático
- ✅ Status de usuário (active, suspended, inactive)
- ✅ Validação de entrada (validation rules)

---

## ✅ Phase 2: Web Dashboard (SPA)

### Telas por Role

#### **Anônimo (Visitante)**
- ✅ Visualizar lista de problemas públicos
- ✅ Filtrar por status/prefeitura/bairro
- ✅ Sem necessidade de login
- ✅ Botão para continuar anônimo

#### **Cidadão (Autenticado)**
- ✅ Dashboard pessoal
- ✅ **Meus Problemas**: listar todos criados
- ✅ **Criar Novo**: formulário completo (título, descrição, endereço, CEP, etc)
- ✅ **Ver Públicos**: todos os problemas cadastrados
- ✅ **Segurança 2FA**: gerar QR code, confirmar, desativar
- ✅ Acompanhar status dos problemas
- ✅ Detalhes completos de cada problema

#### **Admin da Prefeitura**
- ✅ **Gerenciar Problemas**: listar problemas da sua prefeitura
- ✅ **Atualizar Status**: aberto → em_andamento → resolvido → fechado
- ✅ **Assinação**: atribuir problema a servidor específico
- ✅ **Notas Internas**: adicionar comentários não-públicos
- ✅ **Estatísticas**: taxa de resolução, gráficos por status
- ✅ Dashboard com métricas-chave

#### **Super Admin (Gerente Global)**
- ✅ **Gerenciar Usuários**: CRUD completo
  - Criar novo usuário
  - Editar perfil/role/prefeitura
  - Suspender/ativar usuário
  - Deletar usuário
  - Resetar senha
- ✅ **Roles & Permissões**: gerenciar roles, sincronizar permissões
- ✅ **Prefeituras**: CRUD, configurar webhooks
- ✅ **Auditoria**: log completo de todas as ações
  - Filtrar por usuário, ação, modelo, data
  - Visualizar mudanças (antes/depois)

### Tecnologias & Arquitetura
- **Framework**: Blade (Laravel) + Alpine.js
- **UI**: Tailwind CSS v4 (utility-first)
- **State Management**: Alpine.js (x-data, x-model, @click)
- **Storage**: localStorage para tokens e user info
- **API**: REST com Bearer tokens (Sanctum)

### Funcionalidades Cross-Cutting
- ✅ Navegação por abas dentro de cada role
- ✅ Feedback visual (success/error alerts)
- ✅ Loading states em requisições
- ✅ Responsividade (mobile-first)
- ✅ Logout com revogação de token
- ✅ Persistência de sessão (restaurar token ao recarregar)

---

## 📊 Endpoints API (Resumo)

### Públicos
```
POST   /api/auth/register               - Registrar novo cidadão
POST   /api/auth/login                  - Login (com ou sem 2FA)
POST   /api/auth/anonimo                - Sessão anônima
POST   /api/auth/logout                 - Logout e revogação

GET    /api/prefeituras                 - Listar prefeituras
GET    /api/problemas                   - Listar problemas públicos
POST   /api/problemas                   - Reportar novo (anônimo OK)
GET    /api/geocode/reverse             - Reverse geocoding (Nominatim)
GET    /api/cep/{cep}                   - Buscar CEP (ViaCEP)
```

### Autenticados
```
GET    /api/problemas/mine              - Meus problemas
GET    /api/problemas/{id}              - Detalhes problema

POST   /api/2fa/generate                - Gerar QR code 2FA
POST   /api/2fa/confirm                 - Confirmar 2FA
POST   /api/2fa/disable                 - Desativar 2FA
```

### Admin
```
# Super Admin (users, roles, prefeituras)
GET    /api/admin/users                 - Listar usuários
POST   /api/admin/users                 - Criar usuário
GET    /api/admin/users/{id}            - Detalhes usuário
PATCH  /api/admin/users/{id}            - Editar usuário
DELETE /api/admin/users/{id}            - Deletar usuário
POST   /api/admin/users/{id}/reset-password
PATCH  /api/admin/users/{id}/toggle-status

GET    /api/admin/roles                 - Listar roles
POST   /api/admin/roles                 - Criar role
GET    /api/admin/roles/{id}            - Detalhes
PATCH  /api/admin/roles/{id}            - Editar role
DELETE /api/admin/roles/{id}            - Deletar role
GET    /api/admin/permissions           - Listar permissões
POST   /api/admin/roles/{id}/permissions/{perm_id}   - Conceder
DELETE /api/admin/roles/{id}/permissions/{perm_id}   - Revogar

GET    /api/admin/audit-logs            - Logs de auditoria

# Admin Prefeitura
GET    /api/admin/problemas             - Problemas da prefeitura
PATCH  /api/admin/problemas/{id}/status - Atualizar status
```

---

## 🚀 Phase 3: Android App (Próximo)

**Escopo:**
- ✅ Modelos DTO idênticos aos do backend
- ✅ TokenManager (SharedPreferences)
- ✅ Retrofit + OkHttp com interceptor de auth
- ✅ Telas: Login, Anônimo, Cidadão (criar, acompanhar), Admin
- ✅ Autenticação com 2FA
- ✅ Integração com mapa (localização)
- ✅ Webclient e API service

**Data**: Já iniciado, faltando expansão completa

---

## 🔐 Segurança Implementada

1. **Autenticação**
   - bcrypt password hashing
   - Sanctum tokens (expiração padrão)
   - Bearer token headers
   - Revogação de tokens antigos ao login

2. **Autorização**
   - Role-based access control (RBAC)
   - Permission-based granular control
   - Middleware `EnsureRole`, `EnsurePermission`
   - Route protection por middleware

3. **2FA**
   - TOTP (Time-based One-Time Password)
   - QR code via bacon-qr-code
   - 10 backup codes
   - Confirmação obrigatória antes de habilitação

4. **Auditoria**
   - Todos os CRUDs registrados em audit_logs
   - IP address e user agent capturados
   - Histórico de mudanças (JSON)
   - Filtros para análise

5. **Status de Usuário**
   - active, suspended, inactive
   - Verificação em cada login
   - Impede acesso de suspensos

---

## 📂 Estrutura de Diretórios

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/  (11 controllers)
│   │   └── Middleware/   (3 middleware)
│   └── Models/           (6 modelos)
├── database/
│   ├── migrations/       (7 novas)
│   └── seeders/          (2 seeders)
├── routes/
│   ├── api.php           (estruturado por grupos)
│   └── web.php           (SPA fallback)
└── resources/views/
    └── app.blade.php     (dashboard SPA completo)

android/
├── app/src/main/java/com/scs/gestaocidada/
│   ├── data/
│   │   ├── ApiClient.kt
│   │   ├── ApiService.kt
│   │   ├── TokenManager.kt
│   │   └── models/
│   ├── ui/
│   │   ├── screens/
│   │   └── viewmodels/
│   └── MainActivity.kt
```

---

## 📝 Demo Credentials

```
Super Admin:
  Email: super@demo.test
  Pass:  password
  Role:  super

Admin Prefeitura:
  Email: admin@demo.test
  Pass:  password
  Role:  admin
  Prefeitura: São Bento do Sul

Cidadão:
  Email: cidadao@demo.test
  Pass:  password
  Role:  cidadao

Anônimo:
  Sem login
  Acesso readonly aos problemas públicos
```

### 2FA Setup
1. Login como cidadão
2. Ir para "Segurança 2FA"
3. Clicar "Gerar QR Code"
4. Escanear com Google Authenticator/Authy
5. Confirmar com código gerado
6. Guardar 10 backup codes

---

## 🔧 Como Executar Localmente

### Sem Docker
```bash
# Backend
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve --host=0.0.0.0 --port=8000

# Frontend: http://localhost:8000
```

### Com Docker (quando disponível)
```bash
docker compose up -d --build
docker compose exec app php artisan migrate:fresh --seed
# http://localhost
```

### Android
```bash
# Android Studio
Open android/
Ajustar ApiConfig.kt (BASE_URL = "http://10.0.2.2:8000")
Run on emulator
```

---

## 📋 Checklist Próximos Passos

- [ ] **Phase 3**: Expandir Android completo
  - [ ] Telas para todos os 4 roles
  - [ ] Criar problema com mapa
  - [ ] Acompanhar status
  - [ ] Admin: gerenciar problemas
  
- [ ] **Integrações**:
  - [ ] Webhooks para site de prefeitura
  - [ ] SSO com site municipal
  - [ ] API publica para terceiros
  
- [ ] **Testes**:
  - [ ] Testes unitários (backend)
  - [ ] Testes de integração
  - [ ] Testes E2E (Cypress/Playwright)
  
- [ ] **DevOps**:
  - [ ] CI/CD melhorado
  - [ ] Deploy em staging/prod
  - [ ] Rate limiting
  - [ ] Caching (Redis)
  
- [ ] **UX/Melhorias**:
  - [ ] Dark mode
  - [ ] PWA (Progressive Web App)
  - [ ] Notificações push
  - [ ] Relatórios PDF

---

## 👤 Desenvolvido Por

**FRANCISCO CARLOS DE SOUSA**  
**Formação**: ANALISTA DE SISTEMAS - Estácio
**Função/Cargo**: TÉCNICO DE TECNOLOGIA DA INFORMAÇÃO
**Instituição**: INSTITUTO FEDERAL CATARINENSE - São Bento do Sul

---

## 📅 Timeline

- **7 de Janeiro de 2026**: Phase 1 (Backend) + Phase 2 (Web Dashboard) ✅
- **Próximo**: Phase 3 (Android) + Integrações
- **Meta**: Sistema SAS 100% funcional em produção

---

## 📚 Documentação Relacionada

- [SISTEMA_SAS_PHASE1.md](./SISTEMA_SAS_PHASE1.md) - Detalhes técnicos Phase 1
- [SISTEMA_SAS_PHASE2.md](./SISTEMA_SAS_PHASE2.md) - Detalhes técnicos Phase 2
- [PROJECT.md](./PROJECT.md) - Documentação geral do projeto
- [ROADMAP.md](./ROADMAP.md) - Roadmap futuro

---

**Status**: ✅ Phases 1-2 Completas | 🚀 Phase 3 Em Desenvolvimento  
**Última Atualização**: 15 de Janeiro de 2026
