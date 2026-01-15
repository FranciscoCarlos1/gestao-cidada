<template>
  <div class="relatorio-problemas">
    <div class="page-header">
      <div>
        <h1>Relatório de Problemas/Solicitações</h1>
        <p>Análise detalhada de todas as solicitações</p>
      </div>
      <button @click="exportarRelatorio" class="btn btn-primary" :disabled="exporting">
        {{ exporting ? 'Exportando...' : '📥 Exportar CSV' }}
      </button>
    </div>

    <!-- Filtros Avançados -->
    <FilterBar @clear="clearFilters" @apply="loadData">
      <div class="filter-field">
        <label>Data Inicial</label>
        <input v-model="filters.dataInicial" type="date" />
      </div>
      <div class="filter-field">
        <label>Data Final</label>
        <input v-model="filters.dataFinal" type="date" />
      </div>
      <div class="filter-field">
        <label>Status</label>
        <select v-model="filters.status">
          <option value="">Todos</option>
          <option value="pendente">Pendente</option>
          <option value="em_andamento">Em Andamento</option>
          <option value="resolvido">Resolvido</option>
          <option value="cancelado">Cancelado</option>
        </select>
      </div>
      <div class="filter-field">
        <label>Categoria</label>
        <select v-model="filters.categoria">
          <option value="">Todas</option>
          <option value="iluminacao">Iluminação</option>
          <option value="pavimentacao">Pavimentação</option>
          <option value="limpeza">Limpeza</option>
          <option value="saude">Saúde</option>
          <option value="educacao">Educação</option>
          <option value="transporte">Transporte</option>
          <option value="outros">Outros</option>
        </select>
      </div>
      <div class="filter-field">
        <label>Prefeitura</label>
        <select v-model="filters.prefeitura_id">
          <option value="">Todas</option>
          <option v-for="pref in prefeituras" :key="pref.id" :value="pref.id">
            {{ pref.nome }}
          </option>
        </select>
      </div>
    </FilterBar>

    <!-- Estatísticas -->
    <div class="stats-grid">
      <StatCard 
        label="Total de Problemas" 
        :value="stats.total" 
        color="blue"
      >
        <template #icon>📋</template>
      </StatCard>
      
      <StatCard 
        label="Pendentes" 
        :value="stats.pendentes" 
        color="yellow"
        :subtitle="`${calcularPercentual(stats.pendentes, stats.total)}%`"
      >
        <template #icon>⏳</template>
      </StatCard>
      
      <StatCard 
        label="Em Andamento" 
        :value="stats.em_andamento" 
        color="blue"
        :subtitle="`${calcularPercentual(stats.em_andamento, stats.total)}%`"
      >
        <template #icon>🔄</template>
      </StatCard>
      
      <StatCard 
        label="Resolvidos" 
        :value="stats.resolvidos" 
        color="green"
        :subtitle="`${calcularPercentual(stats.resolvidos, stats.total)}%`"
      >
        <template #icon>✅</template>
      </StatCard>
    </div>

    <!-- Gráficos -->
    <div class="charts-grid">
      <div class="chart-card">
        <h3>Distribuição por Status</h3>
        <canvas ref="statusChart"></canvas>
      </div>
      
      <div class="chart-card">
        <h3>Distribuição por Categoria</h3>
        <canvas ref="categoriaChart"></canvas>
      </div>
    </div>

    <!-- Tabela de Dados -->
    <div class="table-section">
      <h3>Detalhamento</h3>
      <DataTable
        :columns="columns"
        :data="problemas"
        :loading="loading"
        :pagination="true"
        :per-page="20"
      >
        <template #cell-status="{ value }">
          <span class="badge" :class="`badge-${value}`">
            {{ formatarStatus(value) }}
          </span>
        </template>

        <template #cell-categoria="{ value }">
          <span class="categoria-badge">{{ value }}</span>
        </template>

        <template #cell-created_at="{ value }">
          {{ formatDate(value) }}
        </template>

        <template #actions="{ row }">
          <button @click="verDetalhes(row)" class="btn-icon" title="Ver detalhes">👁️</button>
        </template>
      </DataTable>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { Chart, registerables } from 'chart.js'
import { useRouter } from 'vue-router'
import DataTable from '../components/DataTable.vue'
import FilterBar from '../components/FilterBar.vue'
import StatCard from '../components/StatCard.vue'
import * as adminApi from '../lib/api'

Chart.register(...registerables)

const router = useRouter()
const loading = ref(false)
const exporting = ref(false)
const problemas = ref([])
const prefeituras = ref([])

const filters = ref({
  dataInicial: '',
  dataFinal: '',
  status: '',
  categoria: '',
  prefeitura_id: ''
})

const stats = ref({
  total: 0,
  pendentes: 0,
  em_andamento: 0,
  resolvidos: 0,
  cancelados: 0
})

const statusChart = ref(null)
const categoriaChart = ref(null)
let chartInstances = []

const columns = [
  { key: 'id', label: 'ID', sortable: true },
  { key: 'titulo', label: 'Título', sortable: true },
  { key: 'categoria', label: 'Categoria', sortable: true },
  { key: 'status', label: 'Status', sortable: true },
  { key: 'prefeitura', label: 'Prefeitura' },
  { key: 'created_at', label: 'Data', sortable: true }
]

onMounted(async () => {
  await loadPrefeituras()
  await loadData()
})

watch(() => filters.value, () => {
  // Auto-aplicar filtros quando mudam
}, { deep: true })

const loadPrefeituras = async () => {
  try {
    prefeituras.value = await adminApi.listPrefeituras()
  } catch (err) {
    console.error('Erro ao carregar prefeituras:', err)
  }
}

const loadData = async () => {
  loading.value = true
  try {
    const data = await adminApi.getProblemasReport(filters.value)
    problemas.value = data.problemas || []
    stats.value = data.stats || { total: 0, pendentes: 0, em_andamento: 0, resolvidos: 0 }
    
    // Atualizar gráficos
    setTimeout(() => createCharts(), 100)
  } catch (err) {
    console.error('Erro ao carregar dados:', err)
  } finally {
    loading.value = false
  }
}

const createCharts = () => {
  // Limpar gráficos anteriores
  chartInstances.forEach(chart => chart.destroy())
  chartInstances = []

  // Gráfico de Status
  if (statusChart.value) {
    chartInstances.push(new Chart(statusChart.value, {
      type: 'doughnut',
      data: {
        labels: ['Pendente', 'Em Andamento', 'Resolvido', 'Cancelado'],
        datasets: [{
          data: [
            stats.value.pendentes,
            stats.value.em_andamento,
            stats.value.resolvidos,
            stats.value.cancelados || 0
          ],
          backgroundColor: ['#fbbf24', '#3b82f6', '#10b981', '#ef4444']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    }))
  }

  // Gráfico de Categoria
  if (categoriaChart.value) {
    const categorias = {}
    problemas.value.forEach(p => {
      categorias[p.categoria] = (categorias[p.categoria] || 0) + 1
    })

    chartInstances.push(new Chart(categoriaChart.value, {
      type: 'bar',
      data: {
        labels: Object.keys(categorias),
        datasets: [{
          label: 'Quantidade',
          data: Object.values(categorias),
          backgroundColor: '#3b82f6'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    }))
  }
}

const clearFilters = () => {
  filters.value = {
    dataInicial: '',
    dataFinal: '',
    status: '',
    categoria: '',
    prefeitura_id: ''
  }
  loadData()
}

const exportarRelatorio = async () => {
  exporting.value = true
  try {
    const csvData = await adminApi.exportProblemasCSV(filters.value)
    
    // Criar blob e fazer download
    const blob = new Blob([csvData], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `relatorio-problemas-${new Date().toISOString().split('T')[0]}.csv`
    a.click()
    window.URL.revokeObjectURL(url)
  } catch (err) {
    alert('Erro ao exportar relatório')
  } finally {
    exporting.value = false
  }
}

const verDetalhes = (problema) => {
  router.push(`/solicitacao/${problema.id}`)
}

const calcularPercentual = (valor, total) => {
  if (!total) return 0
  return Math.round((valor / total) * 100)
}

const formatarStatus = (status) => {
  const labels = {
    pendente: 'Pendente',
    em_andamento: 'Em Andamento',
    resolvido: 'Resolvido',
    cancelado: 'Cancelado'
  }
  return labels[status] || status
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('pt-BR')
}
</script>

<style scoped>
.relatorio-problemas {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  margin: 0 0 0.5rem;
  font-size: 2rem;
  color: #111827;
}

.page-header p {
  margin: 0;
  color: #6b7280;
}

.filter-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-field label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
}

.filter-field input,
.filter-field select {
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.charts-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.chart-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.chart-card h3 {
  margin: 0 0 1rem;
  font-size: 1.125rem;
  color: #111827;
}

.table-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.table-section h3 {
  margin: 0 0 1rem;
  font-size: 1.125rem;
  color: #111827;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
}

.badge-pendente {
  background-color: #fef3c7;
  color: #92400e;
}

.badge-em_andamento {
  background-color: #dbeafe;
  color: #1e40af;
}

.badge-resolvido {
  background-color: #d1fae5;
  color: #065f46;
}

.badge-cancelado {
  background-color: #fee2e2;
  color: #991b1b;
}

.categoria-badge {
  padding: 0.25rem 0.75rem;
  background-color: #f3f4f6;
  border-radius: 12px;
  font-size: 0.75rem;
  color: #374151;
}

.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.25rem;
  padding: 0.25rem;
  transition: transform 0.2s;
}

.btn-icon:hover {
  transform: scale(1.2);
}

.btn {
  padding: 0.625rem 1.25rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background-color: #2563eb;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .charts-grid {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>
