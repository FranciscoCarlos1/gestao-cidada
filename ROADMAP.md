# Roadmap - Gestão Cidadã

## 🚀 Versão 0.1.0 (MVP) - ✅ CONCLUÍDO

### Backend
- [x] API REST com Laravel 11
- [x] Autenticação com Sanctum (JWT)
- [x] Models: User, Prefeitura, Problema
- [x] Migrations e seeders
- [x] Validações e erro handling
- [x] Integração Nominatim (reverse geocoding)
- [x] Integração ViaCEP

### Web
- [x] Dashboard responsivo com Tailwind CSS v4
- [x] Telas: Login, Reportar Problema, Meus Problemas, Admin
- [x] Fetch API com Bearer token
- [x] Carregamento de prefeituras dinâmico
- [x] Status de problema com cores

### Android
- [x] 5 telas com Jetpack Compose
- [x] Navigation System
- [x] Login/Logout com Sanctum
- [x] Google Maps integration
- [x] Form de novo problema
- [x] Lista de meus problemas
- [x] Painel admin

### DevOps
- [x] Docker Compose (DB, API, Nginx)
- [x] GitHub Actions CI/CD
- [x] Workflows: test, build, deploy

---

## 📋 Versão 0.2.0 (Enhancements) - PLANEJADO

### Backend
- [ ] Relatórios detalhados (PDF)
- [ ] Paginação nos endpoints de lista
- [ ] Filtros avançados (status, data, bairro)
- [ ] Notificações por email
- [ ] Sistema de comentários em problemas
- [ ] Upload de fotos
- [ ] Integração com IBGE (dados de municípios)

### Web
- [ ] Dark/Light theme toggle
- [ ] Gráficos de estatísticas (problemas por status, bairro, etc)
- [ ] Exportação de dados (Excel, PDF)
- [ ] Upload de anexos
- [ ] Editor de perfil
- [ ] Histórico de atualizações

### Android
- [ ] Câmera integrada para tirar fotos
- [ ] Offline mode (sincronizar quando voltar online)
- [ ] Notificações push de atualizações de status
- [ ] Share problema via WhatsApp/Email
- [ ] Filtros avançados na busca
- [ ] Widget de problemas próximos

### Infra
- [ ] Kubernetes deployment ready
- [ ] Monitoring (Prometheus/Grafana)
- [ ] Log aggregation (ELK)
- [ ] Rate limiting na API
- [ ] CDN para assets

---

## 🎯 Versão 1.0.0 (Production) - FUTURO

### Estabilidade
- [ ] 95%+ test coverage
- [ ] Load testing (k6/JMeter)
- [ ] Security audit
- [ ] Performance optimization
- [ ] Accessibility (WCAG 2.1)

### Features Premium
- [ ] Multi-idioma (i18n)
- [ ] Integração com sistemas municipais existentes
- [ ] API GraphQL opcional
- [ ] Mobile app iOS (React Native ou SwiftUI)
- [ ] Desktop app (Electron/Tauri)
- [ ] Analytics dashboard

### Monetização (opcional)
- [ ] Planos SaaS para prefeituras
- [ ] Marketplace de soluções
- [ ] Consultoria de implementação

---

## 🐛 Known Issues & Technical Debt

### High Priority
- [ ] Melhorar validação de CEP (alguns formatos não reconhecidos)
- [ ] Performance de mapa com muitos markers
- [ ] Cache de prefeituras não expira

### Medium Priority
- [ ] Documentação de API (Swagger/OpenAPI)
- [ ] Testes E2E (Cypress/Playwright)
- [ ] Refatoração do ProblemaViewModel (muito grande)

### Low Priority
- [ ] Tema Material You no Android
- [ ] Animações de transição suavizadas
- [ ] Tooltip de ajuda em formulários

---

## 📊 Métricas de Sucesso (v1.0)

- [ ] 1000+ usuários ativos mensais
- [ ] 500+ problemas reportados
- [ ] 50+ prefeituras integradas
- [ ] 99.9% uptime
- [ ] <2s response time P95
- [ ] <1MB APK size

---

## 🤝 Contribuindo ao Roadmap

Tem ideias? Abra uma [Discussion](https://github.com/FranciscoCarlos1/gestao-cidada/discussions) ou [Issue](https://github.com/FranciscoCarlos1/gestao-cidada/issues) com label `enhancement`.

---

**Última atualização:** 6 de janeiro de 2026
