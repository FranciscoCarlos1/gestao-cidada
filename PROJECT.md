# Projeto: Gestão Cidadã

Autor e créditos
- Projeto feito e desenvolvido por FRANCISCO CARLOS DE SOUSA, Analista de Sistema pela Estácio, Servidor Público: Técnico de Tecnologia da Informação no INSTITUTO FEDERAL CATARINENSE - São Bento do Sul.

## 📋 Visão Geral

**Gestão Cidadã** é uma plataforma integrada para gerenciamento de problemas urbanos, conectando cidadãos, prefeituras e administradores através de uma API moderna com web e mobile.

### Objetivos
- ✅ Permitir que cidadãos reportem problemas urbanos com geolocalização
- ✅ Fornecer interface web para prefeituras gerenciarem demandas
- ✅ Oferecer app Android nativo com Jetpack Compose
- ✅ Autenticação segura com JWT (Laravel Sanctum)
- ✅ Integração com APIs externas (Nominatim, ViaCEP)

---

## 🏗️ Arquitetura

### Stack Técnico
| Camada | Tecnologia | Versão |
|--------|-----------|--------|
| **Backend** | Laravel + PHP-FPM | 11 / 8.2+ |
| **Banco de Dados** | PostgreSQL | 16 |
| **Web Frontend** | Tailwind CSS v4 + Vanilla JS | - |
| **Mobile** | Jetpack Compose + Kotlin | - |
| **Auth** | Laravel Sanctum (JWT) | - |
| **Servidor** | Nginx | 1.27 |
| **Containers** | Docker Compose | - |

### Estrutura de Pastas
```
gestao-cidada/
├── backend/                          # API Laravel
│   ├── app/Http/Controllers/        # Controllers (Auth, Problema, Admin)
│   ├── app/Models/                  # Models (User, Prefeitura, Problema)
│   ├── database/migrations/         # Migrações DB
│   ├── database/seeders/            # Seeds com dados de teste
│   ├── routes/api.php               # Rotas da API
│   └── resources/views/welcome.blade.php  # Web dashboard
│
├── android/                         # App Kotlin/Compose
│   ├── app/src/main/java/com/scs/gestaocidada/
│   │   ├── ui/screens/             # 5 telas (Login, Main, Form, List, Admin)
│   │   ├── ui/viewmodels/          # ViewModels com StateFlow
│   │   ├── data/models/            # DTOs (Problema, Prefeitura)
│   │   ├── data/ApiClient.kt       # HTTP Client (Retrofit)
│   │   ├── data/TokenManager.kt    # Token Storage (SharedPreferences)
│   │   └── MainActivity.kt         # Entry point + Navigation
│   └── build.gradle.kts            # Gradle config
│
├── .github/workflows/              # CI/CD Pipelines
│   ├── ci-cd.yml                   # Tests + Build
│   ├── deploy.yml                  # Produção
│   └── android-release.yml         # APK Release
│
└── docker-compose.yml              # Orquestração local
```

---

## 🔌 Endpoints da API

### Autenticação
```
POST   /api/auth/login        # Login (email + password → token)
POST   /api/auth/register     # Registro de novo cidadão
POST   /api/auth/logout       # Logout (requer Bearer token)
```

### Públicos (Anônimo ou Autenticado)
```
GET    /api/prefeituras       # Lista de prefeituras
POST   /api/problemas         # Criar problema (anônimo ou com token)
GET    /api/geocode/reverse   # Reverse geocoding (Nominatim)
GET    /api/cep/{cep}         # Consultar CEP (ViaCEP)
```

### Autenticado (Cidadão)
```
GET    /api/problemas/mine    # Meus problemas (precisa token)
```

### Admin (role = 'admin' or 'super')
```
GET    /api/admin/problemas           # Listar problemas (com filtro de status)
PATCH  /api/admin/problemas/{id}/status  # Atualizar status
```

---

## 🚀 Como Subir Local

### Opção 1: Docker (Recomendado)
```bash
git clone https://github.com/FranciscoCarlos1/gestao-cidada.git
cd gestao-cidada

# Subir stack completo (PostgreSQL + Laravel + Nginx)
docker compose up -d --build

# Acessar
# Web: http://localhost:8080
# API: http://localhost:8080/api
```

### Opção 2: Manual (macOS/Linux)
```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed

# Em outro terminal
php artisan serve --host=0.0.0.0 --port=8000
```

### Android
```bash
cd android
# Abrir em Android Studio
# Ajustar ApiConfig.kt se não usar localhost:8080
# Run no emulador (F5 ou ▶ button)
```

---

## 👥 Usuários de Teste

| Email | Senha | Role | Prefeitura |
|-------|-------|------|-----------|
| `super@demo.test` | `password` | super | São Paulo |
| `admin@demo.test` | `password` | admin | São Paulo |
| `cidadao@demo.test` | `password` | cidadao | São Paulo |

---

## 🔐 Autenticação

### Flow
1. **POST /api/auth/login** → `{ token, user: { id, name, email, role } }`
2. **Salvar token** em SharedPreferences (Android) ou localStorage (Web)
3. **Bearer token** em headers: `Authorization: Bearer <token>`
4. **Sanctum** valida em middleware `auth:sanctum`

### Roles
- `super`: Acesso total, master de prefeituras
- `admin`: Gerenciador de problemas da prefeitura
- `cidadao`: Cidadão comum (padrão)

---

## 📦 CI/CD Pipelines

### `ci-cd.yml` (Em todo push/PR)
- ✅ Testes Laravel com PostgreSQL
- ✅ Build Android (gradlew)
- ✅ Lint (PHP Pint, Android Lint)
- ✅ Verificação de vulnerabilidades

### `deploy.yml` (Em push para main)
- 📦 Build otimizado do backend
- 🐳 Docker image build
- 📝 Criar Release no GitHub

### `android-release.yml` (Sob demanda)
- 📱 APK debug + release
- 🔍 Lint
- 📤 Upload artifacts

---

## 💻 Padrões de Código

### Backend (Laravel)
- Controllers em `app/Http/Controllers/`
- Models em `app/Models/`
- Validações com `$request->validate()`
- Migrations com timestamp (ex: `2026_01_05_000001_...`)
- Respostas JSON com `response()->json()`

### Android (Kotlin)
- **Screens** (Composables)
- **ViewModels** com StateFlow (reativo)
- **ApiClient** com Retrofit + Moshi
- **TokenManager** para persistência
- **Models** (DTOs) em `data/models/`

### Web (Vanilla JS)
- Fetch API com Bearer auth
- DOM manipulation minimal
- CSS com Tailwind v4

---

## 🐛 Troubleshooting

| Problema | Solução |
|----------|---------|
| API retorna 401 | Verificar token em `Authorization: Bearer ...` |
| 404 em `/api/*` | Confirmar `bootstrap/app.php` com `withRouting(api: routes/api.php)` |
| App Android não conecta | Ajustar `ApiConfig.BASE_URL` para seu IP/porta |
| PostgreSQL não sobe | Verificar porta 5432 em `docker compose logs db` |
| Tailwind CSS não aplica | Rodar `npm run build` no `backend/` |

---

## 📝 Contribuindo

Veja [CONTRIBUTING.md](./CONTRIBUTING.md) para diretrizes de contribuição.

---

## 📄 Licença

MIT License - veja [LICENSE](./LICENSE) para detalhes.

---

## 📞 Contato

**Mantedor:** Francisco Carlos  
**GitHub:** [@FranciscoCarlos1](https://github.com/FranciscoCarlos1)  
**Repositório:** https://github.com/FranciscoCarlos1/gestao-cidada

---

**Última atualização:** 15 de janeiro de 2026
