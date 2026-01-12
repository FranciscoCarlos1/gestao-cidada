# 📱 Guia Completo de Instalação e Teste - Gestão Cidadã

## ✅ Status da Build
- **APK Debug**: Gerado com sucesso (14.2 MB)
- **Localização**: `android/app/build/outputs/apk/debug/app-debug.apk`
- **Compilação**: Sem erros (60+ erros corrigidos)
- **Dependências**: Google Maps Compose, Retrofit, Firebase configurados

---

## 🚨 Problema Atual: Emulador Sem Espaço
O emulador `emulator-5554` tem 252 pacotes instalados e não consegue instalar o APK (14 MB).

**Erro**: `java.io.IOException: Requested internal only, but not enough space`

---

## 🎯 Soluções para Testar o App

### **Opção 1: Usar Dispositivo Físico (RECOMENDADO)**

#### Passo 1: Habilitar Modo Desenvolvedor no Android
1. Vá em **Configurações** → **Sobre o telefone**
2. Toque **7 vezes** em "Número da compilação"
3. Volte e acesse **Opções do desenvolvedor**
4. Ative **Depuração USB**

#### Passo 2: Conectar via USB
```powershell
# Conectar celular ao computador via USB
# Aceitar permissão de depuração no celular

# Verificar se dispositivo foi detectado
C:\Users\francisco.sousa\AppData\Local\Android\Sdk\platform-tools\adb.exe devices
```

#### Passo 3: Instalar APK
```powershell
cd C:\Users\francisco.sousa\Downloads\gestao-cidada-laravel-android-mapa-cep\android\app\build\outputs\apk\debug

# Instalar no dispositivo físico
C:\Users\francisco.sousa\AppData\Local\Android\Sdk\platform-tools\adb.exe install -r app-debug.apk
```

#### Passo 4: Iniciar App
```powershell
# Abrir app automaticamente
C:\Users\francisco.sousa\AppData\Local\Android\Sdk\platform-tools\adb.exe shell am start -n com.scs.gestaocidada/.MainActivity
```

---

### **Opção 2: Criar Novo Emulador com Mais Espaço**

#### Via Android Studio:
1. Abra **Android Studio**
2. Vá em **Tools** → **Device Manager**
3. Clique **Create Device**
4. Escolha **Pixel 5** ou superior
5. Selecione **System Image**: Android 13 (API 33) ou superior
6. Em **Advanced Settings**:
   - **Internal Storage**: Aumentar para **4096 MB** (4GB)
   - **SD Card**: Adicionar **2048 MB** (2GB)
7. Clique **Finish**

#### Depois de criar:
```powershell
# Listar emuladores disponíveis
C:\Users\francisco.sousa\AppData\Local\Android\Sdk\emulator\emulator.exe -list-avds

# Iniciar novo emulador (substitua NOME_DO_AVD)
C:\Users\francisco.sousa\AppData\Local\Android\Sdk\emulator\emulator.exe -avd NOME_DO_AVD &

# Aguardar inicializar e instalar
C:\Users\francisco.sousa\AppData\Local\Android\Sdk\platform-tools\adb.exe wait-for-device
C:\Users\francisco.sousa\AppData\Local\Android\Sdk\platform-tools\adb.exe install -r app-debug.apk
```

---

### **Opção 3: Usar APK Release (Menor)**

O APK Release é mais otimizado (~8-10 MB vs 14 MB do Debug):

```powershell
# Aguardar build Release terminar (em andamento)
# APK será gerado em:
# android/app/build/outputs/apk/release/app-release-unsigned.apk

# Instalar APK Release
C:\Users\francisco.sousa\AppData\Local\Android\Sdk\platform-tools\adb.exe install -r app-release-unsigned.apk
```

---

### **Opção 4: Instalar via Android Studio**

1. Abra o projeto `android/` no **Android Studio**
2. Conecte dispositivo físico OU inicie emulador com mais espaço
3. Clique no botão **Run ▶️** (Shift+F10)
4. Selecione o dispositivo na lista
5. Android Studio compila e instala automaticamente

---

## 🧪 Roteiro de Testes Completo

### **1. Tela de Login/Splash**
- [ ] App inicia sem crash
- [ ] Logo e branding visíveis
- [ ] Botões "Login" e "Continuar como Anônimo" funcionais

### **2. Fluxo Anônimo**
- [ ] Clicar "Ver Problemas Públicos"
- [ ] Lista de problemas carrega (ou mensagem de vazio)
- [ ] Mapa mostra marcadores de problemas
- [ ] Filtros funcionam (categoria, bairro, status)
- [ ] Clicar em problema abre detalhes

### **3. Fluxo de Login**
**Credenciais de Teste** (do `backend/database/seeders/DatabaseSeeder.php`):

| Usuário       | Email                  | Senha      | Papel       |
|--------------|------------------------|-----------|-------------|
| Super Admin  | super@example.com      | password  | super       |
| Admin        | admin@example.com      | password  | admin       |
| Cidadão      | cidadao@example.com    | password  | cidadao     |

#### Teste de Login:
- [ ] Inserir credenciais válidas
- [ ] Mensagem de erro para credenciais inválidas
- [ ] Redirecionamento após login bem-sucedido
- [ ] Token salvo corretamente

### **4. Tela do Cidadão (após login como cidadão)**
- [ ] Dashboard com estatísticas pessoais
- [ ] Botão "Novo Problema" visível
- [ ] Lista "Meus Problemas" carrega
- [ ] Cada problema mostra foto, status, categoria
- [ ] Botão "Ver no Mapa" funciona
- [ ] Pull-to-refresh atualiza lista

### **5. Formulário de Novo Problema**
- [ ] Botão "Usar Localização Atual" funciona
- [ ] Permissão de localização solicitada
- [ ] Mapa carrega corretamente (Google Maps)
- [ ] Marcador no mapa é arrastável
- [ ] Endereço é preenchido automaticamente via geocoding reverso
- [ ] Campos obrigatórios validados:
  - Título (mínimo 5 caracteres)
  - Descrição (mínimo 10 caracteres)
  - Categoria (dropdown)
- [ ] Foto pode ser anexada (câmera ou galeria)
- [ ] Preview da foto aparece
- [ ] Botão "Enviar" submete problema
- [ ] Loading durante envio
- [ ] Mensagem de sucesso/erro após envio
- [ ] Redirecionamento para lista após sucesso

### **6. Tela Admin (após login como admin)**
- [ ] Lista de todos os problemas da prefeitura
- [ ] Filtros avançados (status, prioridade, período)
- [ ] Botões de ação em cada problema:
  - [ ] Mudar Status (Em Análise → Em Andamento → Resolvido → Arquivado)
  - [ ] Adicionar Comentário Interno
  - [ ] Atribuir para outro admin
  - [ ] Ver histórico de alterações
- [ ] Ordenação (mais recentes, prioridade, bairro)
- [ ] Indicador visual de prioridade (cores)
- [ ] Badge de novos comentários

### **7. Tela Super Admin**
- [ ] Acesso ao Log de Auditoria
- [ ] Tabela com colunas:
  - Data/Hora
  - Usuário (user_id)
  - Ação (model_type)
  - Entidade (model_id)
  - Detalhes
- [ ] Filtro por tipo de ação
- [ ] Filtro por período (data início/fim)
- [ ] Paginação (15 itens por página)
- [ ] Botão "Exportar CSV"

### **8. Tela Meu Perfil**
- [ ] Foto do usuário (ou avatar padrão)
- [ ] Nome e email editáveis
- [ ] Botão "Alterar Senha"
- [ ] Modal de alteração de senha funciona
- [ ] Validação de senha atual
- [ ] Confirmação de nova senha
- [ ] Botão "Salvar" atualiza dados
- [ ] Feedback visual de sucesso/erro

### **9. Configurações / 2FA**
- [ ] Toggle "Ativar Autenticação 2 Fatores"
- [ ] QR Code gerado ao ativar
- [ ] Input para código de 6 dígitos
- [ ] Validação do código TOTP
- [ ] Lista de códigos de backup
- [ ] Botão "Gerar Novos Códigos"
- [ ] Desativar 2FA exige confirmação

### **10. Tela Sobre**
- [ ] Versão do app (ex: 1.0.0)
- [ ] Informações da prefeitura
- [ ] Links para políticas (privacidade, termos)
- [ ] Botão "Verificar Atualizações"
- [ ] Contato do suporte

### **11. Navegação e UX Geral**
- [ ] Bottom Navigation Bar (Home, Mapa, Problemas, Perfil)
- [ ] Transições suaves entre telas
- [ ] Back button funciona corretamente
- [ ] Modo Escuro (se implementado)
- [ ] Indicadores de carregamento visíveis
- [ ] Mensagens de erro claras e em português
- [ ] Feedback tátil ao clicar botões
- [ ] Campos de formulário com placeholder descritivos
- [ ] Validação em tempo real

### **12. Integração com Backend**
- [ ] Trocar URL da API em `ApiConfig.kt`:
  ```kotlin
  // Para testar no localhost
  const val BASE_URL = "http://10.0.2.2:8000/api/" // Emulador
  // const val BASE_URL = "http://SEU_IP:8000/api/" // Dispositivo físico
  ```
- [ ] Verificar backend rodando:
  ```powershell
  cd backend
  docker-compose up -d
  # OU
  php artisan serve --host=0.0.0.0 --port=8000
  ```
- [ ] Testar conectividade:
  ```powershell
  curl http://localhost:8000/api/prefeituras
  ```

### **13. Permissões Android**
- [ ] Localização (GPS) solicitada ao criar problema
- [ ] Câmera solicitada ao tirar foto
- [ ] Armazenamento solicitado ao escolher galeria
- [ ] Internet (configurada no AndroidManifest)

### **14. Casos de Erro**
- [ ] Sem conexão à internet: mensagem clara
- [ ] Timeout de API: retry automático ou manual
- [ ] Token expirado: redirecionar para login
- [ ] Erro 500 do backend: mensagem genérica amigável
- [ ] Geolocalização desabilitada: prompt para ativar
- [ ] Foto muito grande: compressão automática

---

## 📊 Checklist de Qualidade

### **Funcionalidade**
- [ ] Todas as 10+ telas navegáveis
- [ ] CRUD completo de problemas
- [ ] Autenticação Sanctum funciona
- [ ] Upload de imagens (Base64 ou Multipart)
- [ ] Geocoding reverso (Nominatim)
- [ ] Filtros e busca

### **Performance**
- [ ] Carregamento inicial < 3 segundos
- [ ] Transições de tela suaves (60 FPS)
- [ ] Lista com LazyColumn (scroll infinito)
- [ ] Imagens com cache (Coil)
- [ ] Requisições API < 2 segundos

### **UX/UI**
- [ ] Material Design 3 aplicado
- [ ] Cores consistentes (tema da prefeitura)
- [ ] Tipografia legível (mínimo 14sp)
- [ ] Espaçamentos adequados (8dp, 16dp, 24dp)
- [ ] Botões com altura mínima 48dp (acessibilidade)
- [ ] Feedback visual ao clicar (ripple effect)
- [ ] Estados vazios com ilustrações/mensagens
- [ ] Skeleton loading para listas

### **Segurança**
- [ ] Token JWT armazenado de forma segura (DataStore)
- [ ] HTTPS obrigatório em produção
- [ ] Validação de input no frontend E backend
- [ ] Rate limiting configurado
- [ ] Logs sensíveis removidos

---

## 🐛 Relatório de Bugs (Preencher Durante Testes)

| # | Tela/Fluxo | Descrição do Bug | Severidade | Reproduzível? |
|---|-----------|------------------|-----------|---------------|
| 1 |           |                  | Alta/Média/Baixa | Sim/Não |
| 2 |           |                  | Alta/Média/Baixa | Sim/Não |
| 3 |           |                  | Alta/Média/Baixa | Sim/Não |

---

## 📦 Arquivos Gerados

### **Debug APK** (Atual)
- **Caminho**: `android/app/build/outputs/apk/debug/app-debug.apk`
- **Tamanho**: 14.2 MB
- **Assinatura**: Debug (não publicável na Play Store)
- **Uso**: Testes locais

### **Release APK** (Em geração)
- **Caminho**: `android/app/build/outputs/apk/release/app-release-unsigned.apk`
- **Tamanho**: ~8-10 MB (otimizado)
- **Assinatura**: Não assinado (precisa assinar para publicar)
- **Uso**: Testes de performance, distribuição interna

### **Como Assinar Release APK** (Para publicar na Play Store)
```powershell
# 1. Gerar keystore
cd android/app
keytool -genkey -v -keystore gestao-cidada.keystore -alias gestao-cidada-key -keyalg RSA -keysize 2048 -validity 10000

# 2. Configurar gradle (backend/app/build.gradle.kts)
# Adicionar bloco signingConfigs...

# 3. Gerar APK assinado
cd android
.\gradlew.bat assembleRelease

# APK assinado estará em:
# android/app/build/outputs/apk/release/app-release.apk
```

---

## 🔧 Troubleshooting

### **Problema: "App keeps stopping"**
**Solução**: Verificar logs:
```powershell
C:\Users\francisco.sousa\AppData\Local\Android\Sdk\platform-tools\adb.exe logcat -s "GestaoCidada"
```

### **Problema: Mapa não carrega**
**Soluções**:
1. Verificar API Key do Google Maps em `AndroidManifest.xml`
2. Habilitar "Maps SDK for Android" no Google Cloud Console
3. Adicionar fingerprint SHA-1 do keystore nas credenciais da API

### **Problema: API retorna 401 Unauthorized**
**Solução**: Token expirado ou inválido
```kotlin
// Forçar novo login
Session.clearToken()
navController.navigate("login")
```

### **Problema: Fotos não aparecem**
**Soluções**:
1. Verificar permissões no AndroidManifest
2. Testar URL direta da imagem no navegador
3. Verificar CORS no backend Laravel

---

## 📞 Suporte

- **Logs do App**: `adb logcat`
- **Versões**: Kotlin 2.0.20, Compose 1.7.6, Gradle 8.7
- **Backend**: Laravel 11 + PostgreSQL + Docker
- **Documentação**: `README.md` na raiz do projeto

---

**Última Atualização**: Gerado automaticamente após build bem-sucedida
**Status**: APK Debug pronto, Release em geração
