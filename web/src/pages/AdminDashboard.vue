<template>
  <div class="grid">
    <div class="card">
      <h2>Painel administrativo</h2>
      <p class="muted">Gerencie solicitações (triagem/execução). Para SaaS, este painel é o que o cliente paga para usar.</p>
      <div class="row">
        <button class="btn" @click="load" :disabled="loading">{{ loading ? 'Carregando...' : 'Carregar solicitações' }}</button>
      </div>
    </div>

    <div class="card">
      <h3>Solicitações (públicas)</h3>
      <p class="muted">Neste MVP, usamos a listagem pública. Você pode evoluir para rotas admin específicas (triagem, atribuição, SLA etc.).</p>

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
import { ref } from 'vue'
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
</script>
