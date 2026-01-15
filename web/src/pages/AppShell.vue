<template>
  <div class="grid" style="grid-template-columns:260px 1fr; align-items:start">
    <aside class="card">
      <div style="margin-bottom:10px">
        <div class="muted">Logado como</div>
        <div><b>{{ auth.me?.email || '—' }}</b></div>
        <div class="badge">{{ roleLabel }}</div>
      </div>

      <div class="grid" style="gap:8px">
        <RouterLink class="btn secondary" to="/app/cidadao">Painel do cidadão</RouterLink>
        <RouterLink v-if="['admin','prefeitura','super'].includes(auth.role)" class="btn secondary" to="/app/admin">Painel administrativo</RouterLink>

        <hr style="border:0;border-top:1px solid rgba(255,255,255,.10);margin:10px 0"/>

        <RouterLink v-if="auth.role==='super'" class="btn secondary" to="/app/super/users">Usuários</RouterLink>
        <RouterLink v-if="auth.role==='super'" class="btn secondary" to="/app/super/prefeituras">Prefeituras</RouterLink>
        <RouterLink v-if="auth.role==='super'" class="btn secondary" to="/app/super/auditoria">Auditoria</RouterLink>
      </div>

      <p class="muted" style="margin-top:12px;font-size:12px">
        Observação: este painel é um MVP de UI para testar as rotas e perfis. Você pode evoluir com design system depois.
      </p>
    </aside>

    <section>
      <RouterView />
    </section>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '../stores/auth'
const auth = useAuthStore()
const roleLabel = computed(() => auth.role || 'cidadao')
</script>
