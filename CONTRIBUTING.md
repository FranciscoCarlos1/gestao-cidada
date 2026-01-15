# 🤝 Contribuindo para Gestão Cidadã

Obrigado por querer contribuir! Este documento fornece diretrizes para tornar o processo o mais suave possível.

## 📋 Antes de Começar

1. **Faça um fork** do repositório
2. **Clone seu fork**: `git clone https://github.com/SEU_USERNAME/gestao-cidada.git`
3. **Crie uma branch**: `git checkout -b feature/sua-feature`
4. **Instale dependências**: Veja [PROJECT.md](./PROJECT.md#-como-subir-local)

---

## 🔄 Workflow de Contribuição

### 1. Desenvolva sua feature
```bash
# Crie uma branch descritiva
git checkout -b feature/adicionar-filtro-status
# ou
git checkout -b fix/corrigir-validacao-cep
```

### 2. Teste localmente
```bash
# Backend
cd backend && php artisan test

# Android (no Android Studio)
# Build → Run no emulador

# Web
# Abrir http://localhost:8080 e testar manualmente
```

### 3. Commit com mensagens claras
```bash
# Formato: tipo(escopo): mensagem
git commit -m "feat(admin): adicionar filtro por bairro"
git commit -m "fix(auth): corrigir erro de token expirado"
git commit -m "docs(readme): atualizar instruções de setup"
```

**Tipos de commit:**
- `feat`: Nova feature
- `fix`: Bug fix
- `docs`: Documentação
- `style`: Formatação de código
- `refactor`: Refatoração
- `test`: Testes
- `chore`: Tarefas (deps, config, etc)

### 4. Push e crie um Pull Request
```bash
git push origin feature/sua-feature
```

Acesse https://github.com/FranciscoCarlos1/gestao-cidada/pulls e clique em "New Pull Request"

---

## ✅ Checklist de PR

Antes de submeter, certifique-se que:

- [ ] **Código testado** - Rodar testes localmente (`php artisan test` ou Android tests)
- [ ] **Linter passou** - `vendor/bin/pint` (Laravel) ou Android Lint
- [ ] **Sem merge conflicts** - Branch atualizada com `main`
- [ ] **Mensagens commit claras** - Seguir formato acima
- [ ] **Documentação atualizada** - Se mudou features públicas
- [ ] **Sem hardcoded secrets** - Usar `.env` ou variáveis de ambiente
- [ ] **Branch descritiva** - Evitar nomes genéricos como `update` ou `fix`

---

## 📚 Padrões de Código

### Backend (Laravel/PHP)
```php
// ✅ Bom
class ProblemaController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['required', 'string'],
        ]);

        return response()->json(Problema::create($validated), 201);
    }
}

// ❌ Evitar
class ProblemaController extends Controller
{
    public function store(Request $request)
    {
        $problema = new Problema();
        $problema->titulo = $request->titulo;
        // ... sem validação, sem mensagens de erro
    }
}
```

### Android (Kotlin/Compose)
```kotlin
// ✅ Bom
@Composable
fun LoginScreen(
    onLoginSuccess: (String) -> Unit,
    viewModel: AuthViewModel = viewModel()
) {
    val email by remember { mutableStateOf("") }
    // ... com StateFlow, error handling
}

// ❌ Evitar
@Composable
fun LoginScreen() {
    // ... sem viewModel, sem estado reativo, hardcoded URLs
}
```

### Web (Vanilla JS)
```javascript
// ✅ Bom
async function loginUser(email, password) {
    try {
        const response = await fetch('/api/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        if (!response.ok) throw new Error('Login failed');
        return await response.json();
    } catch (error) {
        console.error('Error:', error);
    }
}

// ❌ Evitar
function login() {
    // ... sem error handling, sem fetch, sem headers
}
```

---

## 🏗️ Estrutura de Pastas

**Respeite a estrutura existente:**

```
backend/
  ├── app/Http/Controllers/  ← Nova controller aqui
  ├── app/Models/            ← Novo model aqui
  ├── database/migrations/   ← Nova migração aqui
  └── routes/api.php         ← Registrar rota aqui

android/
  └── app/src/main/java/com/scs/gestaocidada/
      ├── ui/screens/        ← Nova screen aqui
      ├── ui/viewmodels/     ← Novo ViewModel aqui
      └── data/models/       ← Nova DTO aqui
```

---

## 🧪 Testes

### Backend
```bash
cd backend

# Rodar todos os testes
php artisan test

# Rodar teste específico
php artisan test --filter LoginTest

# Com cobertura
php artisan test --coverage
```

### Android
```bash
# No Android Studio
# Clicar em "Run" → "Run Tests"
# ou rodar via terminal
./gradlew test
```

---

## 🔍 Code Review

Um mantenedor revisará seu PR em até 48h. Possíveis comentários:

- **Sugestões de refatoração** - Melhore o código
- **Requests de testes** - Adicione testes se necessário
- **Documentação** - Documente métodos públicos
- **Performance** - Otimize se necessário

**Responda aos comentários** e **faça push de novos commits** na mesma branch.

---

## 🐛 Relatando Issues

### Título claro
❌ "Bug no app"  
✅ "Android app crashes ao carregar lista de problemas"

### Descrição completa
```markdown
## Descrição
App crashes ao tentar listar problemas do usuário.

## Steps para reproduzir
1. Abrir app
2. Login com cidadao@demo.test
3. Clicar em "Meus Problemas"

## Expected
Listar problemas do usuário

## Actual
App fecha com erro

## Logs
```
E/AndroidRuntime: ...
```

## Env
- Phone: Pixel 5 Emulator
- OS: Android 13
- App version: 0.1.0
```

---

## 💬 Discussões

- **Dúvidas?** Abra uma [Discussion](https://github.com/FranciscoCarlos1/gestao-cidada/discussions)
- **Sugestões?** Abra uma [Issue](https://github.com/FranciscoCarlos1/gestao-cidada/issues) com label `enhancement`
- **Security issue?** Email para admin@example.com (não abra issue pública)

---

## 📖 Referências

- [Projeto README](./README.md)
- [Documentação técnica](./PROJECT.md)
- [Laravel Docs](https://laravel.com/docs)
- [Jetpack Compose Guide](https://developer.android.com/jetpack/compose)
- [Git Workflow](https://www.atlassian.com/br/git/workflows)

---

**Obrigado por contribuir! 🎉**
