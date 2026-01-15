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
import { useAuthStore } from './stores/auth'

const routes = [
  { path: '/', component: Home },
  { path: '/problemas', component: Tickets },
  { path: '/novo', component: NewTicket },
  { path: '/login', component: Login },

  { path: '/app', component: AppShell, meta: { auth: true }, children: [
    { path: '', redirect: '/app/cidadao' },
    { path: 'cidadao', component: CitizenDashboard, meta: { auth: true } },
    { path: 'admin', component: AdminDashboard, meta: { auth: true, roles: ['admin','prefeitura','super'] } },
    { path: 'super/users', component: SuperUsers, meta: { auth: true, roles: ['super'] } },
    { path: 'super/prefeituras', component: SuperPrefeituras, meta: { auth: true, roles: ['super'] } },
    { path: 'super/auditoria', component: SuperAudit, meta: { auth: true, roles: ['super'] } },
  ]},
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
