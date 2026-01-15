# 🧪 Guia de Teste - Gestão Cidadã

## 🚀 Iniciar o Sistema

### 1. Subir os containers
```bash
cd c:\Users\francisco.sousa\Downloads\gestao-cidada-saas
docker compose up -d
```

### 2. Aguardar inicialização
- PostgreSQL: ~5 segundos
- Laravel: ~10 segundos
- Vite: ~15 segundos

### 3. Verificar status
```bash
docker compose ps
```

Você deve ver:
- ✅ db (postgres:16)
- ✅ backend (php-fpm)
- ✅ nginx (reverse proxy)
- ✅ web (vite dev server)

---

## 🔐 Login & Teste

### Credenciais de Teste

| Tipo | Email | Senha | Acesso |
|------|-------|-------|--------|
| **Super Admin** | super@demo.test | Super@12345 | Tudo |
| **Admin** | admin.sbs@demo.test | Admin@12345 | Relatórios |
| **Cidadão** | cidadao.centro@demo.test | Cidadao@12345 | Solicitações |

### Acessar o Sistema

1. **Frontend:** http://localhost:5173
2. **Clique em "Login"**
3. **Use uma credencial acima**
4. **Clique em "Entrar"**

---

## 📊 Teste - Super Admin Dashboard

### 1. Acessar Dashboard
```
Login com: super@demo.test / Super@12345
URL: http://localhost:5173/super/dashboard
```

### 2. Verificar Cards
- Total de Usuários: 5
- Total de Problemas: 3
- Total de Prefeituras: 2
- Usuários Ativos: 4

### 3. Verificar Gráficos
- **Doughnut Chart:** Distribuição de status dos problemas
- **Pie Chart:** Distribuição de roles dos usuários
- **Line Chart:** Problemas nos últimos 7 dias

### 4. Atividades Recentes
Mostrar últimas 10 ações no sistema

---

## 👥 Teste - Gerenciar Usuários

### 1. Abrir página
```
URL: http://localhost:5173/super/usuarios
```

### 2. Listar usuários
- Tabela com 5 usuários
- Colunas: ID, Nome, Email, Roles, Status
- Paginação (10 por página)

### 3. Criar novo usuário
```
Clique em "+ Novo Usuário"
- Nome: João Silva
- Email: joao@test.test
- Senha: Test@12345
- Role: admin
- Prefeitura: Bento do Sul
- Status: Ativo
Clique em "Criar"
```

### 4. Editar usuário
```
Clique no ícone de editar
Altere o nome para "João Silva Jr."
Clique em "Salvar"
```

### 5. Desativar usuário
```
Clique no ícone de status (ativar/desativar)
Status deve mudar para "Inativo"
```

### 6. Filtros
```
- Buscar por nome: "joão"
- Filtrar por role: "admin"
- Filtrar por prefeitura: "Bento do Sul"
- Filtrar por status: "Ativo"
```

---

## 🔐 Teste - Gerenciar Roles

### 1. Abrir página
```
URL: http://localhost:5173/super/roles
```

### 2. Listar roles
- super
- admin
- prefeitura
- cidadao

### 3. Editar role
```
Clique em "admin"
Veja a lista de permissões
Marque/desmarque permissões
Clique em "Salvar"
```

### 4. Criar nova role
```
Clique em "+ Nova Role"
- Nome: moderador
- Descrição: Moderador de conteúdo
- Permissões: (selecione algumas)
Clique em "Criar"
```

---

## 📈 Teste - Relatórios

### 1. Relatório de Problemas
```
URL: http://localhost:5173/relatorios/problemas
```

**Funcionalidades:**
- Tabela com 3 problemas
- Filtros: Status, Categoria, Prefeitura, Data
- Gráficos: Status, Categorias
- Botão "Exportar CSV"

**Teste:**
```
1. Filtrar por status: "Aberto"
2. Clique em "Exportar CSV"
3. Verifique download de problemas_YYYY-MM-DD.csv
```

### 2. Relatório de Usuários
```
URL: http://localhost:5173/relatorios/usuarios
```

**Funcionalidades:**
- Tabela com 5 usuários
- Filtros: Role, Prefeitura, Status
- Gráficos: Distribuição de roles
- Botão "Exportar CSV"

### 3. Relatório de Auditoria
```
URL: http://localhost:5173/relatorios/auditoria
```

**Funcionalidades:**
- Log de todas ações (create, update, delete)
- Filtros: Usuário, Modelo, Ação, Data
- Botão "Exportar CSV"

**Teste:**
```
1. Faça alguma ação (criar, editar, deletar)
2. Volte ao relatório
3. Veja a ação registrada no topo
```

---

## 🗺️ Teste - Solicitações (Cidadão)

### 1. Logout como Super Admin
```
Clique no avatar > Logout
```

### 2. Login como Cidadão
```
Email: cidadao.centro@demo.test
Senha: Cidadao@12345
```

### 3. Listar solicitações
```
URL: http://localhost:5173/solicitacoes
Deve mostrar as 3 solicitações de teste
```

### 4. Criar nova solicitação
```
Clique em "+ Nova Solicitação"
```

**Formulário:**
- Título: "Buraco na rua"
- Descrição: "Há um grande buraco na rua..."
- Categoria: Infraestrutura
- Localização: (use GPS ou mapa)
- Anexos: (deixe vazio para teste)

**Localização:**
```
Opção 1 - GPS automático:
  Clique em "📍 Usar minha localização"
  Permita acesso à localização
  
Opção 2 - Marcar no mapa:
  Clique no mapa para selecionar local
  
Opção 3 - Buscar por CEP:
  Digite CEP: 89250-000 (Bento do Sul)
  Pressione Enter
```

### 5. Visualizar detalhe
```
Clique em uma solicitação
Veja todos os detalhes
Status, histórico, comentários (se houver)
```

---

## 🔌 Teste - Endpoints da API

### 1. Obter Token de Autenticação

```bash
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "super@demo.test",
    "password": "Super@12345"
  }'
```

**Resposta (salve o token):**
```json
{
  "token": "sua_token_aqui",
  "user": {
    "id": 1,
    "name": "Super Admin",
    "email": "super@demo.test",
    "roles": [...]
  }
}
```

### 2. Testar Endpoints do Dashboard

```bash
TOKEN="seu_token_aqui"

# Estatísticas
curl -X GET http://localhost:8080/api/admin/dashboard/stats \
  -H "Authorization: Bearer $TOKEN"

# Atividades recentes
curl -X GET http://localhost:8080/api/admin/activities \
  -H "Authorization: Bearer $TOKEN"
```

### 3. Listar Usuários

```bash
TOKEN="seu_token_aqui"

curl -X GET 'http://localhost:8080/api/admin/users?per_page=10' \
  -H "Authorization: Bearer $TOKEN"
```

### 4. Criar Novo Usuário

```bash
TOKEN="seu_token_aqui"

curl -X POST http://localhost:8080/api/admin/users \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "name": "Teste API",
    "email": "api@test.test",
    "password": "Test@12345",
    "password_confirmation": "Test@12345",
    "status": "active",
    "prefeitura_id": 1,
    "roles": [3]
  }'
```

### 5. Obter Relatório de Problemas

```bash
TOKEN="seu_token_aqui"

curl -X GET 'http://localhost:8080/api/admin/reports/problemas?per_page=10' \
  -H "Authorization: Bearer $TOKEN"
```

### 6. Exportar Relatório em CSV

```bash
TOKEN="seu_token_aqui"

curl -X GET 'http://localhost:8080/api/admin/reports/problemas/export' \
  -H "Authorization: Bearer $TOKEN" \
  > problemas.csv
```

---

## 🐛 Troubleshooting

### ❌ Problema: Containers não iniciam
```bash
# Verificar logs
docker compose logs -f backend

# Reiniciar
docker compose restart
```

### ❌ Problema: Porta 5433 em uso
```bash
# Encontrar processo na porta
lsof -i :5433

# Matar processo
kill -9 <PID>
```

### ❌ Problema: Frontend não carrega
```bash
# Limpar cache
rm -rf web/node_modules/.vite

# Reiniciar
docker compose restart web
```

### ❌ Problema: Login não funciona
```bash
# Verificar migrations
docker compose exec backend php artisan migrate:status

# Re-seedar
docker compose exec backend php artisan migrate:fresh --seed
```

---

## ✅ Checklist de Teste

| Item | Status |
|------|--------|
| Docker containers rodando | ✅ |
| Frontend em http://localhost:5173 | ✅ |
| Login como super admin | ✅ |
| Dashboard carregando | ✅ |
| Listar usuários | ✅ |
| Criar usuário | ✅ |
| Editar usuário | ✅ |
| Relatório problemas | ✅ |
| Exportar CSV | ✅ |
| Auditoria registrando | ✅ |
| APIs respondendo | ✅ |

---

## 📝 Notas Importantes

1. **Dados de Teste:** São resetados toda vez que você roda `migrate --seed`
2. **Tokens Expiram:** TTL de 24 horas (configurável em sanctum.php)
3. **Auditoria:** Registra automaticamente todas as ações (create, update, delete)
4. **Permissões:** Super admin tem acesso a tudo, outros roles têm restrições
5. **Exportação:** CSV pode ser aberto no Excel/Google Sheets

---

## 🎯 Próximos Passos

1. **Testes Unitários:** `php artisan test`
2. **Testes E2E:** Cypress/Playwright
3. **Monitoramento:** Add logging e APM
4. **CI/CD:** Deploy automático

---

**Última atualização:** 15 de janeiro de 2026  
**Versão:** 1.0.0 - STABLE
