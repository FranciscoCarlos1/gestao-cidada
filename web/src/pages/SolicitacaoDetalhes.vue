<template>
  <div class="solicitacao-detalhes-container">
    <!-- Loading -->
    <div v-if="loading" class="loading">
      <p>Carregando detalhes da solicitação...</p>
    </div>

    <!-- Erro -->
    <div v-if="error" class="alert alert-error">
      {{ error }}
      <RouterLink to="/solicitacoes" class="btn btn-secondary">
        Voltar para lista
      </RouterLink>
    </div>

    <!-- Detalhes da Solicitação -->
    <div v-if="!loading && solicitacao" class="details-container">
      <div class="header-section">
        <div class="title-row">
          <h1>{{ solicitacao.titulo }}</h1>
          <span :class="['status-badge', `status-${solicitacao.status}`]">
            {{ formatStatus(solicitacao.status) }}
          </span>
        </div>
        <p class="id-info">#{{ solicitacao.id }}</p>
      </div>

      <!-- Informações Básicas -->
      <div class="info-card">
        <h3>Informações Gerais</h3>
        <div class="info-grid">
          <div class="info-item">
            <strong>Categoria:</strong>
            <span>{{ formatCategoria(solicitacao.categoria) }}</span>
          </div>
          <div class="info-item">
            <strong>Status:</strong>
            <span>{{ formatStatus(solicitacao.status) }}</span>
          </div>
          <div class="info-item">
            <strong>Criada em:</strong>
            <span>{{ formatDataLonga(solicitacao.created_at) }}</span>
          </div>
          <div class="info-item">
            <strong>Última atualização:</strong>
            <span>{{ formatDataLonga(solicitacao.updated_at) }}</span>
          </div>
        </div>
      </div>

      <!-- Descrição -->
      <div class="info-card">
        <h3>Descrição</h3>
        <p class="description">{{ solicitacao.descricao }}</p>
      </div>

      <!-- Localização -->
      <div class="info-card">
        <h3>Localização</h3>
        <div class="location-details">
          <p>
            <strong>Endereço:</strong><br />
            {{ solicitacao.endereco }}
            <span v-if="solicitacao.bairro">, {{ solicitacao.bairro }}</span>
          </p>
          <p v-if="solicitacao.latitude && solicitacao.longitude">
            <strong>Coordenadas:</strong><br />
            {{ solicitacao.latitude.toFixed(6) }}, {{ solicitacao.longitude.toFixed(6) }}
          </p>
        </div>
      </div>

      <!-- Mapa -->
      <div v-if="solicitacao.latitude && solicitacao.longitude && showMap" class="info-card">
        <h3>Mapa</h3>
        <div id="map-element-details" class="map"></div>
      </div>

      <!-- Timeline de Atualizações -->
      <div class="info-card">
        <h3>Histórico</h3>
        <div class="timeline">
          <div class="timeline-item">
            <div class="timeline-date">{{ formatDataLonga(solicitacao.created_at) }}</div>
            <div class="timeline-content">
              <div class="timeline-title">Solicitação criada</div>
              <div class="timeline-desc">
                Status: <span class="status-badge status-pendente">Pendente</span>
              </div>
            </div>
          </div>
          <div v-if="solicitacao.status !== 'pendente'" class="timeline-item">
            <div class="timeline-date">{{ formatDataLonga(solicitacao.updated_at) }}</div>
            <div class="timeline-content">
              <div class="timeline-title">Status atualizado</div>
              <div class="timeline-desc">
                Status: <span :class="['status-badge', `status-${solicitacao.status}`]">
                  {{ formatStatus(solicitacao.status) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Ações -->
      <div class="actions-section">
        <div class="actions">
          <RouterLink to="/solicitacoes" class="btn btn-secondary">
            ← Voltar para lista
          </RouterLink>
          <button
            v-if="isOwner"
            @click="editSolicitacao"
            class="btn btn-secondary"
          >
            ✏️ Editar
          </button>
          <button
            v-if="isOwner"
            @click="deleteSolicitacao"
            :disabled="deleting"
            class="btn btn-danger"
          >
            {{ deleting ? 'Deletando...' : '🗑️ Deletar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { api } from '../lib/api'

let mapInstance = null

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const solicitacao = ref(null)
const loading = ref(false)
const error = ref('')
const showMap = ref(false)
const deleting = ref(false)

const isOwner = computed(() => {
  return solicitacao.value && auth.me && solicitacao.value.user_id === auth.me.id
})

function formatStatus(status) {
  const map = {
    'pendente': 'Pendente',
    'em_analise': 'Em Análise',
    'em_andamento': 'Em Andamento',
    'resolvido': 'Resolvido'
  }
  return map[status] || status
}

function formatCategoria(cat) {
  const map = {
    'infraestrutura': 'Infraestrutura',
    'iluminacao': 'Iluminação',
    'limpeza': 'Limpeza',
    'transito': 'Trânsito',
    'saneamento': 'Saneamento',
    'saude': 'Saúde',
    'educacao': 'Educação',
    'outro': 'Outro'
  }
  return map[cat] || cat
}

function formatDataLonga(data) {
  if (!data) return '-'
  return new Date(data).toLocaleString('pt-BR')
}

async function fetchSolicitacao() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get(`/problemas/${route.params.id}`)
    solicitacao.value = data
    showMap.value = true
    setTimeout(() => initMap(), 100)
  } catch (e) {
    error.value = e?.response?.data?.message || 'Erro ao carregar solicitação'
    console.error(e)
  } finally {
    loading.value = false
  }
}

function initMap() {
  if (!solicitacao.value?.latitude || !solicitacao.value?.longitude) return
  
  if (typeof window.L === 'undefined') {
    console.warn('Leaflet não carregado')
    return
  }

  const mapElement = document.getElementById('map-element-details')
  if (!mapElement) return

  if (!mapInstance) {
    mapInstance = window.L.map('map-element-details').setView(
      [solicitacao.value.latitude, solicitacao.value.longitude],
      16
    )

    window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors',
      maxZoom: 19,
    }).addTo(mapInstance)

    window.L.marker([solicitacao.value.latitude, solicitacao.value.longitude])
      .addTo(mapInstance)
      .bindPopup(solicitacao.value.titulo)
      .openPopup()
  }
}

function editSolicitacao() {
  router.push(`/solicitacao/${route.params.id}/editar`)
}

async function deleteSolicitacao() {
  if (!confirm('Tem certeza que deseja deletar esta solicitação?')) return

  deleting.value = true
  try {
    await api.delete(`/problemas/${route.params.id}`)
    router.push('/solicitacoes')
  } catch (e) {
    alert(e?.response?.data?.message || 'Erro ao deletar solicitação')
  } finally {
    deleting.value = false
  }
}

// Carregar Leaflet dinamicamente
onMounted(() => {
  if (!window.L) {
    const link = document.createElement('link')
    link.rel = 'stylesheet'
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css'
    document.head.appendChild(link)

    const script = document.createElement('script')
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js'
    document.head.appendChild(script)
  }

  fetchSolicitacao()
})
</script>

<style scoped>
.solicitacao-detalhes-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px 0;
}

.loading {
  text-align: center;
  padding: 40px 20px;
  color: #a1afc7;
}

.alert {
  padding: 14px 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.alert-error {
  background: rgba(239, 68, 68, 0.1);
  border-color: rgba(239, 68, 68, 0.3);
  color: #fca5a5;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.header-section {
  margin-bottom: 24px;
}

.title-row {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 8px;
}

.title-row h1 {
  margin: 0;
  font-size: 28px;
  color: #e9eefc;
  flex: 1;
}

.id-info {
  margin: 0;
  color: #a1afc7;
  font-size: 13px;
}

.status-badge {
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
}

.status-pendente {
  background: rgba(239, 68, 68, 0.1);
  color: #fca5a5;
}

.status-em_analise {
  background: rgba(59, 130, 246, 0.1);
  color: #93c5fd;
}

.status-em_andamento {
  background: rgba(34, 197, 94, 0.1);
  color: #86efac;
}

.status-resolvido {
  background: rgba(168, 85, 247, 0.1);
  color: #d8b4fe;
}

.info-card {
  background: #101e3d;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
}

.info-card h3 {
  margin-top: 0;
  margin-bottom: 16px;
  color: #e9eefc;
  font-size: 16px;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.info-item strong {
  color: #a1afc7;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-item span {
  color: #e9eefc;
  font-size: 14px;
}

.description {
  margin: 0;
  color: #a1afc7;
  line-height: 1.6;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.location-details p {
  margin: 12px 0;
  color: #a1afc7;
  line-height: 1.6;
}

.location-details strong {
  color: #e9eefc;
}

.map {
  width: 100%;
  height: 300px;
  background: #0f1a33;
  border-radius: 8px;
  overflow: hidden;
}

.timeline {
  position: relative;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 8px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: rgba(37, 99, 235, 0.3);
}

.timeline-item {
  position: relative;
  margin-bottom: 20px;
  padding-left: 32px;
}

.timeline-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 4px;
  width: 18px;
  height: 18px;
  background: #2563eb;
  border: 3px solid #101e3d;
  border-radius: 50%;
}

.timeline-date {
  color: #a1afc7;
  font-size: 12px;
  margin-bottom: 4px;
}

.timeline-title {
  color: #e9eefc;
  font-weight: 600;
  margin-bottom: 6px;
}

.timeline-desc {
  color: #a1afc7;
  font-size: 13px;
}

.actions-section {
  margin-top: 32px;
}

.actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
  font-weight: 500;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #64748b;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background: #475569;
  transform: translateY(-2px);
}

.btn-danger {
  background: rgba(239, 68, 68, 0.2);
  color: #fca5a5;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.btn-danger:hover:not(:disabled) {
  background: rgba(239, 68, 68, 0.3);
  transform: translateY(-2px);
}

@media (max-width: 768px) {
  .solicitacao-detalhes-container {
    padding: 12px;
  }

  .title-row {
    flex-direction: column;
    align-items: flex-start;
  }

  .title-row h1 {
    font-size: 22px;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }

  .actions {
    flex-direction: column;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
