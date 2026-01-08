# Gestão Cidadã (Laravel + Android)

[![CI](https://github.com/FranciscoCarlos1/gestao-cidada/actions/workflows/ci.yml/badge.svg)](https://github.com/FranciscoCarlos1/gestao-cidada/actions/workflows/ci.yml)
[![Android Release](https://github.com/FranciscoCarlos1/gestao-cidada/actions/workflows/android-release.yml/badge.svg)](https://github.com/FranciscoCarlos1/gestao-cidada/actions/workflows/android-release.yml)

> Projeto feito e desenvolvido por
> FRANCISCO CARLOS DE SOUSA — Analista de Sistemas pela Estácio;
> Servidor Público: Técnico de Tecnologia da Informação no INSTITUTO FEDERAL CATARINENSE - São Bento do Sul.

Projeto completo (código-fonte) do **Gestão Cidadã**:
- **Backend API** em **Laravel** (PostgreSQL, autenticação via Sanctum, perfis: `super`, `admin`, `cidadao`)
- **App Android** em **Kotlin + Jetpack Compose** consumindo a API (login, listar e registrar problemas)

> Observação: por ser um template gerado, o projeto **não inclui a pasta `vendor/`** do Laravel nem o cache do Gradle.  
> Basta rodar os comandos de instalação abaixo.

---

## Documentação

- Resumo geral: [SISTEMA_SAS_RESUMO.md](SISTEMA_SAS_RESUMO.md)
- Phase 1 (Backend): [SISTEMA_SAS_PHASE1.md](SISTEMA_SAS_PHASE1.md)
- Phase 2 (Web SPA): [SISTEMA_SAS_PHASE2.md](SISTEMA_SAS_PHASE2.md)
- Projeto (visão geral): [PROJECT.md](PROJECT.md)
- Roadmap: [ROADMAP.md](ROADMAP.md)

---

## 1) Requisitos

### Backend
- PHP 8.2+
- Composer
- PostgreSQL 14+
- (Opcional) Docker + Docker Compose

### Android
- Android Studio (Hedgehog/Igual ou mais novo)
- JDK 17 (recomendado)

---

## 2) Subir tudo com Docker (recomendado)

Na pasta raiz do projeto:

```bash
docker compose up -d --build
```

Isso sobe:
- PostgreSQL
- Laravel (php-fpm)
- Nginx

A API ficará em: `http://localhost:8080`

---

## 3) Backend sem Docker (local)

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=0.0.0.0 --port=8000
```

API: `http://localhost:8000`

---

## 4) Android

1. Abra a pasta `android/` no Android Studio  
2. Ajuste a URL da API em:  
   `android/app/src/main/java/com/scs/gestaocidada/data/ApiConfig.kt`

3. Rode no emulador ou dispositivo.

---

## 5) Usuários e dados de teste (seed)

Ao rodar `php artisan migrate --seed`, são criados:

- **Super Admin**
  - email: `super@gestaocidada.local`
  - senha: `12345678`

- **Admin (Prefeitura SBS)**
  - email: `admin@sbs.local`
  - senha: `12345678`

- **Cidadão**
  - email: `cidadao@demo.local`
  - senha: `12345678`

E também:
- Prefeitura “São Bento do Sul” (SC)
- 3 problemas de exemplo

---

## 6) Endpoints principais

- `POST /api/auth/register`
- `POST /api/auth/login`
- `POST /api/auth/logout` (auth)
- `GET  /api/prefeituras`
- `POST /api/problemas` (público — permite anônimo)
- `GET  /api/problemas/mine` (auth)
- `GET  /api/admin/problemas` (admin/super)
- `PATCH /api/admin/problemas/{id}/status` (admin/super)

---

## 7) Observação importante (Android + Emulador)

Se você estiver rodando a API no seu PC e testando no **emulador**, use:
- `http://10.0.2.2:8000` (quando `php artisan serve`)
- `http://10.0.2.2:8080` (quando via Docker)

Se for em aparelho físico, use o IP da sua máquina na rede.


## Geolocalização (bairro/rua/CEP)
- O app Android tem o botão **“Usar minha localização”** na tela *Novo problema*.
- Ele captura latitude/longitude e chama a API:
  - `GET /api/geocode/reverse?lat=-26.25&lng=-49.38`
- A API faz **reverse geocoding** via **Nominatim (OpenStreetMap)** e devolve bairro/rua/número/CEP/cidade/UF (quando disponível).
- Os campos podem ser ajustados manualmente antes de enviar.

---

**CI / Releases**

- Workflows ativos:
  - `.github/workflows/ci.yml` — pipeline consolidado: testa Backend (Laravel + Postgres) e builda Android (Debug), publica APK como artifact.
  - `.github/workflows/android-release.yml` — workflow manual (workflow_dispatch) para gerar APK Release; se segredos de assinatura estiverem configurados, assina e publica o APK assinado.

- Para executar o build de Release manualmente:
  1) Vá em Actions → Android Release → Run workflow
  2) (Opcional) Configure segredos para assinatura antes de rodar:
     - `ANDROID_KEYSTORE_BASE64`
     - `ANDROID_KEYSTORE_PASSWORD`
     - `ANDROID_KEY_ALIAS`
     - `ANDROID_KEY_PASSWORD`

- Gerar keystore local (opcional):
  ```bash
  keytool -genkeypair -v -keystore release.keystore -alias mykey -keyalg RSA -keysize 2048 -validity 10000
  base64 release.keystore | tr -d '\n'
  ```
  Cole o conteúdo em `ANDROID_KEYSTORE_BASE64` (Secrets).


