# Instruções rápidas para agentes (Copilot / coding agents)

Objetivo: tornar um agente produtivo rapidamente neste repositório monorepo: backend Laravel + app Android.

- **Arquitetura grande (por onde começar)**
  - Pasta `backend/`: API Laravel (Postgres). Principais pontos: `backend/routes/api.php`, controllers em `backend/app/Http/Controllers`, modelos em `backend/app/Models` (ex.: Prefeitura.php, Problema.php, User.php).
  - Pasta `android/`: app Kotlin + Jetpack Compose. Config e URL da API em `android/app/src/main/java/com/scs/gestaocidada/data/ApiConfig.kt`.
  - Orquestração: `docker-compose.yml` sobe PostgreSQL + PHP-FPM + Nginx. Prefira Docker para reproduzibilidade.

- **Fluxos de dados e integrações**
  - Autenticação: Laravel Sanctum (pacote presente em `backend/vendor/laravel/sanctum`). Endpoints em `backend/routes/api.php` (ex.: /api/auth/*).
  - Persistência: migrations em `backend/database/migrations` e seeders em `backend/database/seeders/DatabaseSeeder.php` (contém usuários de teste e Prefeitura exemplo).
  - Geocode: API expõe `/api/geocode/reverse` usando Nominatim; Android chama esse endpoint ao capturar localização.
  - Comunicação app ← API: Android usa JSON REST direto; alguns endpoints públicos permitem envio anônimo (`POST /api/problemas`).

- **Comandos úteis (dev)**
  - Subir todo o ambiente (recomendado):
    - `docker compose up -d --build` (executar na raiz do repositório)
  - Backend (sem Docker):
    - `cd backend`
    - `cp .env.example .env && composer install && php artisan key:generate && php artisan migrate --seed && php artisan serve --host=0.0.0.0 --port=8000`
  - Android: abrir `android/` no Android Studio; ajustar a URL em ApiConfig.kt e rodar no emulador.

- **Padrões e convenções do projeto**
  - Separação clara: API Laravel isolada em `backend/` — alterações de infraestrutura ou pacotes PHP ficam ali.
  - Seeds criam contas demo (super/admin/cidadao) — utilize para testes rápidos (credenciais no README).
  - As migrations usam timestamps padrão Laravel; não renomeie sem migrar/seedar localmente.

- **Arquivos-chave para referência rápida**
  - Rotas e endpoints: `backend/routes/api.php`
  - Models principais: `backend/app/Models/Prefeitura.php`, `backend/app/Models/Problema.php`, `backend/app/Models/User.php`
  - Migrations: `backend/database/migrations/2026_*` (ex.: create_prefeituras_table)
  - Android API config: `android/app/src/main/java/com/scs/gestaocidada/data/ApiConfig.kt`
  - Docker compose: `docker-compose.yml`

- **O que não adivinhar / confirmar com o time**
  - Versões exatas de PHP/JDK em produção — use as recomendadas no README (PHP 8.2+, JDK 17) e confirme antes de mudanças maiores.
  - URL pública/variáveis de ambiente em produção — não hardcode credenciais.

- **Exemplos concretos de tarefas que um agente pode executar imediatamente**
  - Atualizar ou documentar um endpoint: abra `backend/routes/api.php`, localize rota, leia controller correspondente em `backend/app/Http/Controllers`.
  - Ajustar a URL da API no Android para testes locais: editar `android/.../ApiConfig.kt` e executar no emulador.
  - Reproduzir problemas de autenticação: rodar `php artisan migrate --seed` e usar as credenciais do README.

Se algo aqui estiver faltando ou impreciso, diga qual parte você quer que eu detalhe (ex.: fluxo de criação de Problema, testes unitários, ou scripts Docker). 
