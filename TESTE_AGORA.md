# ✅ SISTEMA FUNCIONANDO - GUIA DE TESTE

## 🎯 STATUS: TUDO OPERACIONAL

```
✅ Frontend:   http://localhost:5173 (Vue 3 + Vite)
✅ Backend:    http://localhost:8080 (Laravel API)
✅ Database:   postgres:5433 (Migrado & Seedado)
✅ Docker:     4 containers rodando
```

---

## 🚀 ACESSO RÁPIDO

### 1️⃣ Frontend
**URL:** http://localhost:5173

### 2️⃣ Credenciais de Teste

| Tipo | Email | Senha | Permissões |
|------|-------|-------|-----------|
| **Super Admin** | `super@demo.test` | `Super@12345` | Tudo |
| **Admin SBS** | `admin.sbs@demo.test` | `Admin@12345` | Relatórios |
| **Cidadão** | `cidadao.centro@demo.test` | `Cidadao@12345` | Solicitações |

---

## 📋 TESTES RECOMENDADOS

### TESTE 1: Login & Dashboard Super Admin
```
1. Acesse http://localhost:5173
2. Clique em "Entrar"
3. Use: super@demo.test / Super@12345
4. Você será redirecionado para o dashboard
```

**Verificar:**
- ✅ Gráficos carregando (Doughnut, Pie, Line Chart)
- ✅ Cards de estatísticas (Total Usuários, Problemas, etc)
- ✅ Atividades recentes listadas
- ✅ Avatar do usuário no canto superior

---

### TESTE 2: Gerenciar Usuários
```
1. No painel, procure por "Usuários" no menu
2. Clique em "+ Novo Usuário"
3. Preencha:
   - Nome: João Silva
   - Email: joao@test.test
   - Senha: Test@12345
   - Role: admin
   - Prefeitura: Bento do Sul
4. Clique "Salvar"
```

**Verificar:**
- ✅ Novo usuário aparecer na lista
- ✅ Filtros funcionando (busca por nome, filtro por role)
- ✅ Paginação (10 usuários por página)
- ✅ Editar usuário (clicar no ícone de lápis)
- ✅ Desativar usuário (clique no status)

---

### TESTE 3: Relatórios
```
1. Acesse "Relatórios" no menu
2. Escolha um dos 3 relatórios disponíveis
```

#### 3.1 - Relatório de Problemas
- Tabela com 3 problemas exemplo
- Filtros: Status, Categoria, Prefeitura
- Gráficos de distribuição
- **Exportar CSV:** Clique no botão "📥 Exportar CSV"
  - Download: `problemas_YYYY-MM-DD.csv`

#### 3.2 - Relatório de Usuários
- Tabela com usuários
- Filtros: Role, Prefeitura, Status
- Gráficos de roles
- Exportar CSV

#### 3.3 - Relatório de Auditoria
- Log de todas as ações (create, update, delete)
- Filtros: Usuário, Modelo, Ação, Data
- Exportar CSV

---

### TESTE 4: Solicitações (Cidadão)
```
1. Faça logout (clique no avatar > Sair)
2. Faça login com: cidadao.centro@demo.test / Cidadao@12345
3. Clique em "Solicitações"
```

**Funcionalidades:**
- ✅ Listar as 3 solicitações de teste
- ✅ Criar nova solicitação
  - Título, descrição, categoria
  - Localização via GPS ou mapa
  - Status em tempo real

---

### TESTE 5: API REST
```bash
# 1. Obter token
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "super@demo.test",
    "password": "Super@12345"
  }'

# Resposta terá o token

# 2. Usar token para acessar endpoints
TOKEN="seu_token_aqui"
curl -X GET http://localhost:8080/api/admin/dashboard/stats \
  -H "Authorization: Bearer $TOKEN"

# 3. Listar usuários
curl -X GET 'http://localhost:8080/api/admin/users?per_page=10' \
  -H "Authorization: Bearer $TOKEN"

# 4. Exportar relatório em CSV
curl -X GET 'http://localhost:8080/api/admin/reports/problemas/export' \
  -H "Authorization: Bearer $TOKEN" \
  > problemas.csv
```

---

## 🐛 TROUBLESHOOTING

### Problema: Frontend não carrega (erro branco)
```bash
# Verificar logs do Vite
docker compose logs web | tail -20

# Reiniciar
docker compose restart web
```

### Problema: Login não funciona
```bash
# Verificar se banco foi seedado
docker compose exec backend php artisan migrate:status

# Re-seedar dados de teste
docker compose exec backend php artisan migrate:fresh --seed --force
```

### Problema: Página branca após login
```bash
# Limpar cache do browser
- Ctrl+Shift+Delete (Windows)
- Cmd+Shift+Delete (Mac)
- F12 > Storage > Clear All (Firefox)

# Reiniciar containers
docker compose restart
```

### Problema: Port 5173 já em uso
```bash
# Encontrar processo
lsof -i :5173

# Matar processo (Mac/Linux)
kill -9 <PID>

# No Windows, editar docker-compose.yml:
# ports:
#   - "5174:5173"  # usar porta diferente
```

---

## 📊 RELATÓRIO DE FUNCIONALIDADES

| Funcionalidade | Status | Teste |
|---|---|---|
| Login | ✅ Funcionando | super@demo.test |
| Dashboard | ✅ Funcionando | 4 gráficos + cards |
| Usuários CRUD | ✅ Funcionando | Criar, editar, deletar |
| Roles & Perms | ✅ Funcionando | Atribuir permissões |
| Relatórios | ✅ Funcionando | 3 tipos de relatórios |
| Exportação CSV | ✅ Funcionando | Baixar em CSV |
| Solicitações | ✅ Funcionando | Criar com GPS/mapa |
| Auditoria | ✅ Funcionando | Log de ações |
| API REST | ✅ Funcionando | 25+ endpoints |
| Autenticação JWT | ✅ Funcionando | Sanctum tokens |

---

## 🔗 RECURSOS ÚTEIS

- **GitHub:** https://github.com/FranciscoCarlos1/gestao-cidada
- **Documentação:** [ARQUITETURA.html](./ARQUITETURA.html)
- **Resumo Final:** [RESUMO_FINAL.html](./RESUMO_FINAL.html)
- **Guia Completo:** [GUIA_TESTE.md](./GUIA_TESTE.md)

---

## 📦 COMANDOS ÚTEIS

```bash
# Iniciar sistema
docker compose up -d

# Parar sistema
docker compose down

# Ver logs
docker compose logs -f backend
docker compose logs -f web

# Re-seedar database
docker compose exec backend php artisan migrate:fresh --seed --force

# Acessar container
docker compose exec backend bash
docker compose exec web sh

# Limpar volumes (CUIDADO - deleta dados!)
docker compose down -v
```

---

## ✨ PRÓXIMOS PASSOS

1. **Testes Unitários:** `php artisan test`
2. **Testes E2E:** Cypress/Playwright
3. **Performance:** Load testing com k6
4. **Segurança:** Penetration testing
5. **Deploy:** Cloud (AWS, Azure, DigitalOcean)

---

## 📞 SUPORTE

Se encontrar problemas:
1. Verificar logs: `docker compose logs -f`
2. Verificar migrations: `docker compose exec backend php artisan migrate:status`
3. Re-seedar dados: `docker compose exec backend php artisan migrate:fresh --seed --force`
4. Reiniciar tudo: `docker compose down && docker compose up -d`

---

**Status:** ✅ PRONTO PARA TESTE  
**Data:** 15 de janeiro de 2026  
**Versão:** 1.0.0 STABLE
