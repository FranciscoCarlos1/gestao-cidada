<template>
  <div class="card">
    <h2>Auditoria (Super Admin)</h2>
    <p class="muted">Carrega logs via <code>/api/admin/audit-logs</code>.</p>
    <button class="btn secondary" @click="load" :disabled="loading">{{ loading ? 'Carregando...' : 'Atualizar' }}</button>
    <p v-if="error" class="muted" style="color:#ffb4b4">{{ error }}</p>

    <div v-for="a in items" :key="a.id" class="card" style="background:#0b1530;margin-top:10px">
      <div class="row" style="justify-content:space-between">
        <div><b>{{ a.action || a.acao || 'Ação' }}</b></div>
        <span class="badge">{{ a.created_at || '' }}</span>
      </div>
      <div class="muted">{{ a.description || a.descricao || '' }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { api } from '../lib/api'
const items = ref([])
const loading = ref(false)
const error = ref('')
async function load(){
  loading.value=true; error.value=''
  try{
    const { data } = await api.get('/admin/audit-logs')
    items.value = data?.data || data || []
  }catch(e){
    error.value = e?.response?.data?.message || 'Falha ao carregar'
  }finally{
    loading.value=false
  }
}
onMounted(load)
</script>
