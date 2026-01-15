# 🔐 Credenciais de Teste - Sistema Gestão Cidadã

## 📋 Usuários Disponíveis

### 👑 Super Admin (Acesso Total)
- **Email:** `super@demo.test`
- **Senha:** `Super@12345`
- **Descrição:** Acesso completo ao sistema, pode gerenciar todas as prefeituras

---

### 🏛️ Administrador - São Bento do Sul
- **Email:** `admin.sbs@demo.test`
- **Senha:** `Admin@12345`
- **Prefeitura:** Prefeitura Municipal de São Bento do Sul
- **Descrição:** Administrador da prefeitura de São Bento do Sul

---

### 🏛️ Administrador - Joinville
- **Email:** `admin.joinville@demo.test`
- **Senha:** `Admin@12345`
- **Prefeitura:** Prefeitura Municipal de Joinville
- **Descrição:** Administrador da prefeitura de Joinville

---

### 👤 Cidadão - Centro
- **Email:** `cidadao.centro@demo.test`
- **Senha:** `Cidadao@12345`
- **Prefeitura:** São Bento do Sul
- **Descrição:** Cidadão da região central

---

### 👤 Cidadão - Zona Norte
- **Email:** `cidadao.zonanorte@demo.test`
- **Senha:** `Cidadao@12345`
- **Prefeitura:** Joinville
- **Descrição:** Cidadão da zona norte

---

## 🌐 URLs de Acesso

### Backend API
- **URL:** http://localhost:8080
- **Login Endpoint:** `POST /api/auth/login`

### Frontend Web (se disponível)
- **URL:** http://localhost:5173

### Banco de Dados PostgreSQL
- **Host:** localhost
- **Porta:** 5433
- **Database:** gestao_cidada
- **Usuário:** gestao
- **Senha:** gestao

---

## 🧪 Testando o Login via API

### Usando cURL (PowerShell):
```powershell
# Super Admin
$body = @{
    email = "super@demo.test"
    password = "Super@12345"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8080/api/auth/login" -Method POST -Body $body -ContentType "application/json"
```

### Usando Thunder Client / Postman:
```json
POST http://localhost:8080/api/auth/login
Content-Type: application/json

{
    "email": "super@demo.test",
    "password": "Super@12345"
}
```

---

## 📊 Dados de Teste Criados

- ✅ 5 usuários (1 super admin, 2 admins, 2 cidadãos)
- ✅ 2 prefeituras (São Bento do Sul e Joinville)
- ✅ 3 problemas cadastrados

---

## 🔄 Resetar Dados de Teste

Para recriar todos os dados de teste:
```bash
docker compose exec backend php artisan migrate:fresh --seed --force
```

---

## 💡 Dicas

1. Todos os emails terminam com `@demo.test`
2. Senhas seguem o padrão: `Tipo@12345` (primeira letra maiúscula)
3. Use o Super Admin para testar funcionalidades administrativas
4. Use os Cidadãos para testar o fluxo de criação de problemas
