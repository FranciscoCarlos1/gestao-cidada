<template>
  <div class="card">
    <h2>Prefeituras (Super Admin)</h2>
    <p class="muted">Lista pública via <code>/api/prefeituras</code> (CRUD admin já existe no backend).</p>
    <button class="btn secondary" @click="load" :disabled="loading">{{ loading ? 'Carregando...' : 'Atualizar' }}</button>
    <p v-if="error" class="muted" style="color:#ffb4b4">{{ error }}</p>

    <div v-for="p in items" :key="p.id" class="card" style="background:#0b1530;margin-top:10px">
      <div class="row" style="justify-content:space-between">
        <div><b>{{ p.nome || p.name || 'Prefeitura' }}</b></div>
        <span class="badge">#{{ p.id }}</span>
      </div>
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
    const { data } = await api.get('/prefeituras')
    items.value = data?.data || data || []
  }catch(e){
    error.value = e?.response?.data?.message || 'Falha ao carregar'
  }finally{
    loading.value=false
  }
}
onMounted(load)
</script>
