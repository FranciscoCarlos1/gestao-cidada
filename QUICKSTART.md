# Gestão Cidadã — execução local (Docker)

## Requisitos
- Docker Desktop (Windows/Mac) ou Docker Engine (Linux)

## Subir tudo
Na raiz do projeto:

```bash
docker compose up -d --build
```

Aguarde o primeiro build (backend instala Composer automaticamente).

## Acessos
- **API (Laravel)**: http://localhost:8080
- **Web (Vue 3)**: http://localhost:5173

## Banco de dados (Postgres)
- Host: localhost
- Porta: 5433
- DB: gestao_cidada
- Usuário: gestao
- Senha: gestao

## Login
Os usuários de teste são criados pelo `db:seed` no primeiro boot do backend.
Se você alterou seeds, rode:

```bash
docker compose exec backend php artisan db:seed --force
```

> Dica: se não souber os logins, abra `backend/database/seeders/DatabaseSeeder.php` e confira os e-mails/senhas.

## Observações importantes
- Este pacote inclui um **MVP de front-end** (Vue 3) com:
  - login
  - painel cidadão/admin/super (rotas protegidas por role)
  - listagens via API
- A evolução para **SaaS com cobrança Efí** requer a inclusão das credenciais Efí e implementação de webhooks/assinaturas.
  Este ZIP prepara a estrutura de UI e isolamento por prefeitura/role no backend.
