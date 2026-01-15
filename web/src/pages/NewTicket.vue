<template>
  <div class="card" style="max-width:820px;margin:0 auto">
    <h2>Abrir solicitação</h2>
    <p class="muted">MVP: envia para a API. Ajuste campos conforme seu backend.</p>

    <form class="grid" @submit.prevent="submit">
      <div class="row">
        <div style="flex:1">
          <label>Título</label>
          <input v-model="titulo" required />
        </div>
        <div style="width:220px">
          <label>CEP</label>
          <input v-model="cep" placeholder="00000-000" />
        </div>
      </div>

      <div>
        <label>Descrição</label>
        <textarea v-model="descricao" rows="4" required />
      </div>

      <div class="row">
        <button class="btn" :disabled="loading">{{ loading ? 'Enviando...' : 'Enviar' }}</button>
        <span v-if="ok" class="badge">Enviado!</span>
        <span v-if="error" class="muted" style="color:#ffb4b4">{{ error }}</span>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { api } from '../lib/api'

const titulo = ref('')
const descricao = ref('')
const cep = ref('')

const loading = ref(false)
const error = ref('')
const ok = ref(false)

async function submit(){
  loading.value=true; error.value=''; ok.value=false
  try{
    // endpoint anonymous might differ; try /problemas/anonimo then /problemas
    const payload = { titulo: titulo.value, descricao: descricao.value, cep: cep.value }
    try{
      await api.post('/problemas/anonimo', payload)
    } catch {
      await api.post('/problemas', payload)
    }
    ok.value=true
    titulo.value=''; descricao.value=''; cep.value=''
  }catch(e){
    error.value = e?.response?.data?.message || 'Falha ao enviar'
  }finally{
    loading.value=false
  }
}
</script>
