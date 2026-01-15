<template>
  <div class="grid">
    <div class="card">
      <h2>Solicitações públicas</h2>
      <div class="row">
        <button class="btn secondary" @click="load" :disabled="loading">{{ loading ? 'Carregando...' : 'Atualizar' }}</button>
      </div>
    </div>

    <div class="card">
      <h3>Lista</h3>
      <p v-if="error" class="muted" style="color:#ffb4b4">{{ error }}</p>
      <div v-for="p in items" :key="p.id" class="card" style="background:#0b1530;margin-top:10px">
        <div class="row" style="justify-content:space-between">
          <div><b>#{{ p.id }}</b> — {{ p.titulo || p.title || 'Solicitação' }}</div>
          <span class="badge">{{ p.status || '—' }}</span>
        </div>
        <div class="muted" style="margin-top:6px">{{ p.descricao || p.description || '' }}</div>
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
    const { data } = await api.get('/problemas')
    items.value = data?.data || data || []
  }catch(e){
    error.value = e?.response?.data?.message || 'Falha ao carregar'
  }finally{
    loading.value=false
  }
}
onMounted(load)
</script>
