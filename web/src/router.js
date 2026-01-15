import { createRouter, createWebHistory } from 'vue-router'
import Home from './pages/Home.vue'
import Login from './pages/Login.vue'
import AppShell from './pages/AppShell.vue'
import CitizenDashboard from './pages/CitizenDashboard.vue'
import AdminDashboard from './pages/AdminDashboard.vue'
import Tickets from './pages/Tickets.vue'
import NewTicket from './pages/NewTicket.vue'
import SuperUsers from './pages/SuperUsers.vue'
import SuperPrefeituras from './pages/SuperPrefeituras.vue'
import SuperAudit from './pages/SuperAudit.vue'
import SolicitacoesList from './pages/SolicitacoesList.vue'
import NovasSolicitacoes from './pages/NovasSolicitacoes.vue'
import SolicitacaoDetalhes from './pages/SolicitacaoDetalhes.vue'

// Novas páginas Super Admin
import SuperDashboard from './pages/SuperDashboard.vue'
import SuperUsuarios from './pages/SuperUsuarios.vue'
import SuperRoles from './pages/SuperRoles.vue'
import RelatorioProblemas from './pages/RelatorioProblemas.vue'
import RelatorioUsuarios from './pages/RelatorioUsuarios.vue'
import RelatorioAuditoria from './pages/RelatorioAuditoria.vue'

import { useAuthStore } from './stores/auth'

const routes = [
  { path: '/', component: Home },
  { path: '/problemas', component: Tickets },
  { path: '/novo', component: NewTicket },
  { path: '/login', component: Login },

  // Novas rotas para solicitações
  { path: '/solicitacoes', component: SolicitacoesList, meta: { auth: true } },
  { path: '/solicitacao/nova', component: NovasSolicitacoes, meta: { auth: true } },
  { path: '/solicitacao/:id', component: SolicitacaoDetalhes, meta: { auth: true } },

  { path: '/app', component: AppShell, meta: { auth: true }, children: [
    { path: '', redirect: '/app/cidadao' },
    { path: 'cidadao', component: CitizenDashboard, meta: { auth: true } },
    { path: 'admin', component: AdminDashboard, meta: { auth: true, roles: ['admin','prefeitura','super'] } },
    
    // Rotas antigas (mantidas para compatibilidade)
    { path: 'super/users', component: SuperUsers, meta: { auth: true, roles: ['super'] } },
    { path: 'super/prefeituras', component: SuperPrefeituras, meta: { auth: true, roles: ['super'] } },
    { path: 'super/auditoria', component: SuperAudit, meta: { auth: true, roles: ['super'] } },
  ]},

  // Novas rotas Super Admin (fora do AppShell)
  { path: '/super/dashboard', component: SuperDashboard, meta: { auth: true, roles: ['super'] } },
  { path: '/super/usuarios', component: SuperUsuarios, meta: { auth: true, roles: ['super'] } },
  { path: '/super/roles', component: SuperRoles, meta: { auth: true, roles: ['super'] } },
  
  // Rotas de Relatórios
  { path: '/relatorios/problemas', component: RelatorioProblemas, meta: { auth: true, roles: ['super', 'admin'] } },
  { path: '/relatorios/usuarios', component: RelatorioUsuarios, meta: { auth: true, roles: ['super'] } },
  { path: '/relatorios/auditoria', component: RelatorioAuditoria, meta: { auth: true, roles: ['super'] } },
]

const router = createRouter({ history: createWebHistory(), routes })

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  if (auth.token && !auth.me) await auth.bootstrap()

  if (to.meta?.auth && !auth.isAuthed) return { path: '/login', query: { next: to.fullPath } }

  const roles = to.meta?.roles
  if (roles && roles.length) {
    const r = auth.role || ''
    if (!roles.includes(r)) return { path: '/app/cidadao' }
  }
  return true
})

export default router
