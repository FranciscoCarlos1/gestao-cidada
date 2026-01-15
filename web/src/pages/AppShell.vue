<template>
  <div class="grid" style="grid-template-columns:260px 1fr; align-items:start">
    <aside class="card">
      <div style="margin-bottom:10px">
        <div class="muted">Logado como</div>
        <div><b>{{ auth.me?.email || '—' }}</b></div>
        <div class="badge">{{ roleLabel }}</div>
      </div>

      <div class="grid" style="gap:8px">
        <RouterLink class="btn secondary" to="/app/cidadao">🏠 Painel do cidadão</RouterLink>
        <RouterLink v-if="['admin','prefeitura','super'].includes(auth.role)" class="btn secondary" to="/app/admin">⚙️ Painel administrativo</RouterLink>

        <!-- Menu Super Admin -->
        <template v-if="auth.role==='super'">
          <hr style="border:0;border-top:1px solid rgba(255,255,255,.10);margin:10px 0"/>
          <div class="menu-section-title">Super Admin</div>
          <RouterLink class="btn secondary" to="/super/dashboard">📊 Dashboard</RouterLink>
          <RouterLink class="btn secondary" to="/super/usuarios">👥 Usuários</RouterLink>
          <RouterLink class="btn secondary" to="/super/roles">🔐 Roles & Permissões</RouterLink>
          <RouterLink class="btn secondary" to="/app/super/prefeituras">🏛️ Prefeituras</RouterLink>
          
          <hr style="border:0;border-top:1px solid rgba(255,255,255,.10);margin:10px 0"/>
          <div class="menu-section-title">Relatórios</div>
          <RouterLink class="btn secondary" to="/relatorios/problemas">📋 Problemas</RouterLink>
          <RouterLink class="btn secondary" to="/relatorios/usuarios">👤 Usuários</RouterLink>
          <RouterLink class="btn secondary" to="/relatorios/auditoria">🔍 Auditoria</RouterLink>
        </template>

        <!-- Links antigos mantidos -->
        <template v-if="auth.role==='super'">
          <hr style="border:0;border-top:1px solid rgba(255,255,255,.10);margin:10px 0"/>
          <RouterLink class="btn secondary" to="/app/super/users">(Antigo) Usuários</RouterLink>
          <RouterLink class="btn secondary" to="/app/super/auditoria">(Antigo) Auditoria</RouterLink>
        </template>
      </div>

      <button @click="handleLogout" class="btn danger" style="margin-top:16px;width:100%">
        🚪 Sair
      </button>

      <p class="muted" style="margin-top:12px;font-size:12px">
        Sistema de Gestão Cidadã - Painel completo de Super Admin com relatórios e estatísticas.
      </p>
    </aside>

    <section>
      <RouterView />
    </section>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()

const roleLabel = computed(() => auth.role || 'cidadao')

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.menu-section-title {
  font-size: 0.75rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.5);
  text-transform: uppercase;
  margin-top: 0.5rem;
  padding: 0.25rem 0.5rem;
}</style>