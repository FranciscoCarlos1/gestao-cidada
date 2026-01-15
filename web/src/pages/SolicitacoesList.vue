<template>
  <div class="solicitacoes-container">
    <div class="page-header">
      <h1>Minhas Solicitações</h1>
      <RouterLink to="/solicitacao/nova" class="btn btn-primary">
        + Nova Solicitação
      </RouterLink>
    </div>

    <!-- Busca e Filtros -->
    <div class="filter-section">
      <input
        v-model="searchText"
        type="text"
        placeholder="Buscar por título, categoria, status..."
        class="search-input"
      />
      
      <div class="filter-buttons">
        <button
          v-for="status in statuses"
          :key="status"
          :class="['filter-btn', { active: selectedStatus === status }]"
          @click="selectedStatus = status"
        >
          {{ formatStatus(status) }}
        </button>
      </div>
    </div>

    <!-- Loading e Erro -->
    <div v-if="loading" class="loading">
      <p>Carregando solicitações...</p>
    </div>

    <div v-if="error" class="alert alert-error">
      {{ error }}
      <button @click="fetchSolicitacoes" class="btn-retry">Tentar novamente</button>
    </div>

    <!-- Lista de Solicitações -->
    <div v-if="!loading && filteredSolicitacoes.length > 0" class="solicitacoes-grid">
      <RouterLink
        v-for="sol in filteredSolicitacoes"
        :key="sol.id"
        :to="`/solicitacao/${sol.id}`"
        class="solicitacao-card"
      >
        <div class="card-header">
          <h3>{{ sol.titulo }}</h3>
          <span :class="['status-badge', `status-${sol.status}`]">
            {{ formatStatus(sol.status) }}
          </span>
        </div>

        <div class="card-body">
          <p class="categoria">
            <strong>Categoria:</strong> {{ formatCategoria(sol.categoria) }}
          </p>
          <p class="endereco" v-if="sol.endereco">
            <strong>Local:</strong> {{ sol.endereco }}
          </p>
          <p class="data">
            <strong>Criada em:</strong> {{ formatData(sol.created_at) }}
          </p>
        </div>

        <div class="card-footer">
          <small>ID: #{{ sol.id }}</small>
        </div>
      </RouterLink>
    </div>

    <!-- Vazio -->
    <div v-if="!loading && filteredSolicitacoes.length === 0" class="empty-state">
      <p>{{ emptyMessage }}</p>
      <RouterLink v-if="!selectedStatus" to="/solicitacao/nova" class="btn btn-secondary">
        Criar primeira solicitação
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { api } from '../lib/api'

const solicitacoes = ref([])
const loading = ref(false)
const error = ref('')
const searchText = ref('')
const selectedStatus = ref('')

const statuses = ['', 'pendente', 'em_analise', 'em_andamento', 'resolvido']

const filteredSolicitacoes = computed(() => {
  let filtered = solicitacoes.value

  if (selectedStatus.value) {
    filtered = filtered.filter(s => s.status === selectedStatus.value)
  }

  if (searchText.value) {
    const search = searchText.value.toLowerCase()
    filtered = filtered.filter(s =>
      s.titulo?.toLowerCase().includes(search) ||
      s.categoria?.toLowerCase().includes(search) ||
      s.status?.toLowerCase().includes(search)
    )
  }

  return filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
})

const emptyMessage = computed(() => {
  if (selectedStatus.value) {
    return `Nenhuma solicitação com status "${formatStatus(selectedStatus.value)}"`
  }
  return 'Você ainda não criou nenhuma solicitação'
})

function formatStatus(status) {
  const map = {
    '': 'Todos',
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

function formatData(data) {
  if (!data) return '-'
  return new Date(data).toLocaleDateString('pt-BR')
}

async function fetchSolicitacoes() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/problemas/mine')
    solicitacoes.value = data || data.data || []
  } catch (e) {
    error.value = e?.response?.data?.message || 'Erro ao carregar solicitações'
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchSolicitacoes()
})
</script>

<style scoped>
.solicitacoes-container {
  padding: 20px 0;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}

.page-header h1 {
  margin: 0;
  font-size: 28px;
  color: #e9eefc;
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
  transition: all 0.2s;
}

.btn-primary {
  background: #2563eb;
  color: white;
}

.btn-primary:hover {
  background: #1d4ed8;
  transform: translateY(-2px);
}

.btn-secondary {
  background: #64748b;
  color: white;
}

.btn-secondary:hover {
  background: #475569;
}

.filter-section {
  background: #101e3d;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 24px;
}

.search-input {
  width: 100%;
  padding: 10px 14px;
  background: #0f1a33;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  color: #e9eefc;
  font-size: 14px;
  margin-bottom: 12px;
}

.search-input::placeholder {
  color: rgba(233, 238, 252, 0.5);
}

.filter-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.filter-btn {
  padding: 8px 14px;
  background: #0f1a33;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #a1afc7;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  transition: all 0.2s;
}

.filter-btn:hover {
  border-color: rgba(255, 255, 255, 0.2);
  color: #e9eefc;
}

.filter-btn.active {
  background: #2563eb;
  border-color: #2563eb;
  color: white;
}

.loading,
.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #a1afc7;
}

.alert {
  padding: 14px 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.alert-error {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #fca5a5;
}

.btn-retry {
  padding: 6px 12px;
  background: transparent;
  border: 1px solid #fca5a5;
  color: #fca5a5;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
}

.solicitacoes-grid {
  display: grid;
  gap: 16px;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
}

.solicitacao-card {
  background: #101e3d;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.2s;
  text-decoration: none;
  color: inherit;
  display: flex;
  flex-direction: column;
}

.solicitacao-card:hover {
  border-color: rgba(37, 99, 235, 0.3);
  box-shadow: 0 8px 24px rgba(37, 99, 235, 0.1);
  transform: translateY(-4px);
}

.card-header {
  padding: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}

.card-header h3 {
  margin: 0;
  font-size: 16px;
  color: #e9eefc;
  flex: 1;
}

.status-badge {
  padding: 4px 10px;
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

.card-body {
  padding: 12px 16px;
  flex: 1;
}

.card-body p {
  margin: 8px 0;
  font-size: 13px;
  line-height: 1.5;
}

.categoria,
.endereco,
.data {
  color: #a1afc7;
}

.card-footer {
  padding: 8px 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  color: #7c8ba8;
  font-size: 11px;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }

  .solicitacoes-grid {
    grid-template-columns: 1fr;
  }
}
</style>
