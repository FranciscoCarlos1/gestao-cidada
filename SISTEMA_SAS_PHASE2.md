# Sistema SAS - Phase 2: Web Dashboard Completo

## ✅ Implementado

### 1. **Dashboard SPA (Single Page Application)**
Arquivo: `backend/resources/views/app.blade.php`

**Telas & Funcionalidades:**

#### Anônimo
- ✅ Visualizar problemas públicos
- ✅ Sem necessidade de login
- ✅ Listar por status/prefeitura

#### Cidadão Autenticado
- ✅ Dashboard pessoal
- ✅ Criar novo problema
- ✅ Acompanhar meus problemas
- ✅ Visualizar status
- ✅ Setup 2FA (TOTP + backup codes)
- ✅ Gerenciar perfil

#### Admin da Prefeitura
- ✅ Gerenciar problemas da prefeitura
- ✅ Atualizar status de problemas
- ✅ Assinarem problemas a servidores
- ✅ Adicionar notas internas
- ✅ Estatísticas de resolução
- ✅ Gráficos de status

#### Super Admin
- ✅ Gerenciar usuários (CRUD)
- ✅ Criar/editar/deletar usuários
- ✅ Suspender usuários
- ✅ Resetar senhas
- ✅ Gerenciar roles e permissões
- ✅ Listar prefeituras
- ✅ Ver log de auditoria completo

### 2. **Controllers Criados/Atualizados**
- ✅ `AuditLogController` - endpoint `/api/admin/audit-logs`

### 3. **Tecnologias**
- **Frontend**: Blade + Alpine.js + Tailwind CSS v4
- **State Management**: Alpine.js x-data
- **API**: REST com Bearer tokens (Sanctum)
- **Storage**: localStorage para tokens/user

### 4. **Autenticação & Segurança**
- ✅ Login com email/password
- ✅ 2FA obrigatório (verificação antes de conceder acesso)
- ✅ Registro automático
- ✅ Login anônimo (sessão temporária)
- ✅ Logout com token revogação
- ✅ Bearer token nos headers

## 📋 Estrutura da SPA

```
Login/Register → Anônimo/Cidadão/Admin/Super
    ↓
Dashboard (view baseada em role)
    ├─ Anônimo: Apenas leitura de públicos
    ├─ Cidadão: CRUD problemas + 2FA
    ├─ Admin: Gerenciamento da prefeitura
    └─ Super: Gerenciamento global + auditoria
```

## 🎯 Rotas Web

```
GET  /              → Dashboard SPA
GET  /dashboard     → Dashboard SPA
*    /*             → Fallback to SPA (para roteamento client-side)
```

## 🔌 Endpoints API Utilizados

**Auth:**
- POST /api/auth/login (com totp_code)
- POST /api/auth/register
- POST /api/auth/anonimo
- POST /api/auth/logout

**2FA:**
- POST /api/2fa/generate
- POST /api/2fa/confirm

**Dados:**
- GET /api/prefeituras
- GET /api/problemas
- POST /api/problemas
- GET /api/problemas/{id}
- GET /api/problemas/mine

**Admin:**
- GET /api/admin/users
- POST /api/admin/users
- PATCH /api/admin/users/{id}
- DELETE /api/admin/users/{id}
- PATCH /api/admin/users/{id}/toggle-status
- GET /api/admin/audit-logs
- GET /api/admin/problemas
- PATCH /api/admin/problemas/{id}/status

## 🚀 Como Usar

### Acessar o Dashboard
```
http://localhost:8000/
```

### Demo Accounts
```
Cidadão:
  Email: cidadao@demo.test
  Pass:  password

Admin Prefeitura:
  Email: admin@demo.test
  Pass:  password

Super Admin:
  Email: super@demo.test
  Pass:  password
```

### Configurar 2FA
1. Login como cidadão
2. Ir para aba "Segurança 2FA"
3. Clicar "Gerar QR Code"
4. Escanear com Google Authenticator/Authy
5. Confirmar com código TOTP
6. Guardar códigos de backup

## 🎨 Design

- **Color Scheme**: Tailwind CSS padrão (azul, verde, vermelho)
- **Layout**: Responsivo (mobile-first)
- **Componentes**: Cards, tables, forms, buttons, modals inline
- **Feedback**: Alerts de sucesso/erro, loading states

## ⚠️ Notas Importantes

1. **Storage Local**: Tokens salvos em `localStorage`
   - Nunca guardar em cookies sem `httpOnly`
   - Considerar usar IndexedDB em produção

2. **CORS**: API e frontend mesmo domínio (Blade)
   - Sem necessidade de CORS headers

3. **Refresh**: Página recarrega Sem persistir tokens?
   - Use `window.onload` para restaurar de localStorage (implementado)

4. **Rate Limiting**: Implementar em produção
   - Backend: use `throttle` middleware
   - Frontend: debounce/throttle nas requisições

## 📊 Próximo: Phase 3 (Android App)

Endpoints já estão prontos para Android:
- Mesmas rotas API
- Mesmo sistema de autenticação (Sanctum tokens)
- Mesmos status e regras de negócio
- TokenManager vai usar SharedPreferences em vez de localStorage

---

**Desenvolvido por**: FRANCISCO CARLOS DE SOUSA  
**Instituição**: INSTITUTO FEDERAL CATARINENSE - São Bento do Sul
