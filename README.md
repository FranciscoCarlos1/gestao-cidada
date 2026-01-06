# Gestão Cidadã (Laravel + Android)

> Projeto feito e desenvolvido por
> FRANCISCO CARLOS DE SOUSA — Analista de Sistema pela Estácio; Servidor Público: Técnico de Tecnologia da Informação no INSTITUTO FEDERAL CATARINENSE - São Bento do Sul.

Projeto completo (código-fonte) do **Gestão Cidadã**:
- **Backend API** em **Laravel** (PostgreSQL, autenticação via Sanctum, perfis: `super`, `admin`, `cidadao`)
- **App Android** em **Kotlin + Jetpack Compose** consumindo a API (login, listar e registrar problemas)

> Observação: por ser um template gerado, o projeto **não inclui a pasta `vendor/`** do Laravel nem o cache do Gradle.  
> Basta rodar os comandos de instalação abaixo.

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

- Este repositório inclui workflows GitHub Actions:
  - `.github/workflows/android-build.yml` — executa `assembleDebug` na branch `main` e publica o APK debug como artifact.
  - `.github/workflows/release-on-tag.yml` — ao criar um tag `v*` (ex.: `v1.0.0`) o workflow compila o APK e cria um Release com o APK anexado.

- Para criar um release manualmente via tag:
  ```bash
  git tag v1.0.0
  git push origin v1.0.0
  ```
  O GitHub Actions irá construir e anexar o APK ao release.

**Assinar APKs (opcional)**

Se quiser que o workflow assine o APK automaticamente e anexe a versão assinada ao Release, faça o seguinte:

1. Gere um keystore localmente (ou use o seu existente):

```bash
keytool -genkeypair -v -keystore release.keystore -alias mykey -keyalg RSA -keysize 2048 -validity 10000
```

2. Encode o keystore em base64 e adicione como Secret no repositório (Settings → Secrets → Actions):

```bash
base64 release.keystore | tr -d '\n'  # copie o output
```

Crie os seguintes Secrets:
- `KEYSTORE_BASE64` — conteúdo base64 do keystore
- `KEYSTORE_PASSWORD` — senha do keystore
- `KEY_ALIAS` — alias da chave (ex.: `mykey`)
- `KEY_PASSWORD` — senha da chave (pode ser igual à do keystore)

Após adicionar os secrets, crie/empurre uma tag `v*` (ex.: `v1.0.1`) e o workflow `sign-and-attach.yml` irá assinar o APK e anexá-lo ao Release.


