<template>
  <div class="card" style="max-width:520px;margin:0 auto">
    <h2>Entrar</h2>
    <p class="muted">Use suas credenciais do backend (Laravel).</p>

    <form class="grid" @submit.prevent="onSubmit">
      <div>
        <label>E-mail</label>
        <input v-model="email" type="email" placeholder="seu@email.com" required />
      </div>
      <div>
        <label>Senha</label>
        <input v-model="password" type="password" placeholder="••••••••" required />
      </div>

      <p v-if="auth.error" class="muted" style="color:#ffb4b4">{{ auth.error }}</p>

      <button class="btn" :disabled="auth.loading">
        {{ auth.loading ? 'Entrando...' : 'Entrar' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const email = ref('')
const password = ref('')

async function onSubmit() {
  await auth.login(email.value, password.value)
  router.push(route.query.next || '/app')
}
</script>
