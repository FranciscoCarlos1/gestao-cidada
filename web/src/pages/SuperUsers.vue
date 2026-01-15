<template>
  <div class="card">
    <h2>Usuários (Super Admin)</h2>
    <p class="muted">Lista via <code>/api/admin/users</code>.</p>
    <button class="btn secondary" @click="load" :disabled="loading">{{ loading ? 'Carregando...' : 'Atualizar' }}</button>
    <p v-if="error" class="muted" style="color:#ffb4b4">{{ error }}</p>

    <div v-for="u in users" :key="u.id" class="card" style="background:#0b1530;margin-top:10px">
      <div class="row" style="justify-content:space-between">
        <div><b>{{ u.name || u.nome || 'Usuário' }}</b> — <span class="muted">{{ u.email }}</span></div>
        <span class="badge">{{ (u.role || u.roles?.[0]?.name || '—') }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { api } from '../lib/api'
const users = ref([])
const loading = ref(false)
const error = ref('')
async function load(){
  loading.value=true; error.value=''
  try{
    const { data } = await api.get('/admin/users')
    users.value = data?.data || data || []
  }catch(e){
    error.value = e?.response?.data?.message || 'Falha ao carregar'
  }finally{
    loading.value=false
  }
}
onMounted(load)
</script>
