<template>
  <div class="app">
    <header class="top">
      <div class="brand">
        <strong>Gestão Cidadã</strong>
        <small>Portal & Painel</small>
      </div>
      <nav class="nav">
        <RouterLink to="/">Início</RouterLink>
        <RouterLink to="/problemas">Solicitações</RouterLink>
        <RouterLink v-if="auth.isAuthed" to="/app">Painel</RouterLink>
        <RouterLink v-if="!auth.isAuthed" to="/login">Entrar</RouterLink>
        <button v-if="auth.isAuthed" class="link" @click="logout">Sair</button>
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
import { useAuthStore } from './stores/auth'
const auth = useAuthStore()

async function logout() {
  await auth.logout()
}
</script>

<style>
:root{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Arial,sans-serif}
body{margin:0;background:#0b1220;color:#e9eefc}
a{color:inherit;text-decoration:none;opacity:.9}
a.router-link-active{opacity:1;text-decoration:underline}
.app{min-height:100vh;display:flex;flex-direction:column}
.top{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;background:#0f1a33;border-bottom:1px solid rgba(255,255,255,.08)}
.brand small{display:block;opacity:.7}
.nav{display:flex;gap:14px;align-items:center}
.main{flex:1;padding:18px;max-width:1100px;width:100%;margin:0 auto}
.foot{padding:14px 18px;opacity:.7;border-top:1px solid rgba(255,255,255,.08);background:#0f1a33}
button.link{background:transparent;border:0;color:#e9eefc;cursor:pointer;opacity:.9;padding:0}
.card{background:#101e3d;border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:16px}
.grid{display:grid;gap:12px}
.row{display:flex;gap:10px;flex-wrap:wrap}
input,select,textarea{width:100%;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,.14);background:#0b1530;color:#e9eefc}
label{display:block;font-size:12px;opacity:.8;margin-bottom:6px}
.btn{padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,.14);background:#1b2f63;color:#fff;cursor:pointer}
.btn.secondary{background:#12224a}
.muted{opacity:.75}
.badge{display:inline-block;padding:4px 10px;border-radius:999px;border:1px solid rgba(255,255,255,.16);opacity:.9;font-size:12px}
</style>
