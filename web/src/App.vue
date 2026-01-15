<template>
  <div class="app">
    <header class="top">
      <div class="brand">
        <strong>Gestão Cidadã</strong>
        <small>Portal & Painel</small>
      </div>
      <nav class="nav">
        <RouterLink to="/">Início</RouterLink>
        <RouterLink to="/problemas">Solicitações Públicas</RouterLink>
        <RouterLink v-if="auth.isAuthed" to="/solicitacoes">Minhas Solicitações</RouterLink>
        <RouterLink v-if="auth.isAuthed" to="/app">Painel</RouterLink>
        <div v-if="auth.isAuthed" class="user-section">
          <span class="user-email">{{ auth.me?.email || 'Usuário' }}</span>
          <button class="link logout-btn" @click="logout">Sair</button>
        </div>
        <RouterLink v-if="!auth.isAuthed" to="/login" class="link login-btn">Entrar</RouterLink>
      </nav>
    </header>

    <main class="main">
      <RouterView />
    </main>

    <footer class="foot">
      <small>© {{ new Date().getFullYear() }} Gestão Cidadã • Demo local</small>
    </footer>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'
const auth = useAuthStore()
const router = useRouter()

async function logout() {
  await auth.logout()
  router.push('/')
}
</script>

<style>
:root{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Arial,sans-serif}
body{margin:0;background:#0b1220;color:#e9eefc}
a{color:inherit;text-decoration:none;opacity:.9}
a.router-link-active{opacity:1;text-decoration:underline}
.app{min-height:100vh;display:flex;flex-direction:column}
.top{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;background:#0f1a33;border-bottom:1px solid rgba(255,255,255,.08);flex-wrap:wrap;gap:14px}
.brand small{display:block;opacity:.7}
.nav{display:flex;gap:14px;align-items:center;flex-wrap:wrap}
.user-section{display:flex;align-items:center;gap:10px}
.user-email{font-size:13px;opacity:.8}
.logout-btn{padding:6px 12px;background:#ef4444;border-radius:6px;opacity:1}
.login-btn{padding:8px 14px;background:#2563eb;border-radius:6px;opacity:1}
.main{flex:1;padding:18px;max-width:1100px;width:100%;margin:0 auto}
.foot{padding:14px 18px;opacity:.7;border-top:1px solid rgba(255,255,255,.08);background:#0f1a33}
button.link{background:transparent;border:0;color:#e9eefc;cursor:pointer;opacity:.9;padding:0;font-size:14px}
.card{background:#101e3d;border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:16px}
.grid{display:grid;gap:12px}
.row{display:flex;gap:10px;flex-wrap:wrap}
input,select,textarea{width:100%;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,.14);background:#0b1530;color:#e9eefc}
label{display:block;font-size:12px;opacity:.8;margin-bottom:6px}
.btn{padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,.14);background:#1b2f63;color:#fff;cursor:pointer}
.btn.secondary{background:#12224a}
.muted{opacity:.75}
.badge{display:inline-block;padding:4px 10px;border-radius:999px;border:1px solid rgba(255,255,255,.16);opacity:.9;font-size:12px}

@media (max-width: 768px) {
  .top{flex-direction:column;align-items:flex-start}
  .nav{width:100%;flex-direction:column;gap:8px}
  .nav > *{width:100%;text-align:center}
}
