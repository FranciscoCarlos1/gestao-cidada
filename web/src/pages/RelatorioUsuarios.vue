<template>
  <div class="relatorio-usuarios">
    <div class="page-header">
      <div>
        <h1>Relatório de Usuários</h1>
        <p>Análise de usuários cadastrados</p>
      </div>
      <button @click="exportar" class="btn btn-primary" :disabled="exporting">
        {{ exporting ? 'Exportando...' : '📥 Exportar' }}
      </button>
    </div>

    <FilterBar @clear="clearFilters" @apply="loadData">
      <div class="filter-field">
        <label>Role</label>
        <select v-model="filters.role">
          <option value="">Todas</option>
          <option value="cidadao">Cidadão</option>
          <option value="admin">Admin</option>
          <option value="prefeitura">Prefeitura</option>
          <option value="super">Super</option>
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
      <div class="filter-field">
        <label>Data Inicial</label>
        <input v-model="filters.dataInicial" type="date" />
      </div>
      <div class="filter-field">
        <label>Data Final</label>
        <input v-model="filters.dataFinal" type="date" />
      </div>
    </FilterBar>

    <div class="stats-grid">
      <StatCard label="Total de Usuários" :value="stats.total" color="blue">
        <template #icon>👥</template>
      </StatCard>
      <StatCard label="Cidadãos" :value="stats.cidadaos" color="green">
        <template #icon>👤</template>
      </StatCard>
      <StatCard label="Admins" :value="stats.admins" color="purple">
        <template #icon>🔧</template>
      </StatCard>
      <StatCard label="Novos (30 dias)" :value="stats.novos" color="yellow">
        <template #icon>🆕</template>
      </StatCard>
    </div>

    <div class="chart-section">
      <div class="chart-card">
        <h3>Distribuição por Role</h3>
        <canvas ref="roleChart"></canvas>
      </div>
    </div>

    <div class="table-section">
      <h3>Usuários</h3>
      <DataTable
        :columns="columns"
        :data="usuarios"
        :loading="loading"
        :pagination="true"
        :per-page="20"
      >
        <template #cell-role="{ value }">
          <span class="role-badge" :class="`role-${value}`">{{ value }}</span>
        </template>
        <template #cell-created_at="{ value }">
          {{ formatDate(value) }}
        </template>
      </DataTable>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Chart, registerables } from 'chart.js'
import DataTable from '../components/DataTable.vue'
import FilterBar from '../components/FilterBar.vue'
import StatCard from '../components/StatCard.vue'
import * as adminApi from '../lib/api'

Chart.register(...registerables)

const loading = ref(false)
const exporting = ref(false)
const usuarios = ref([])
const prefeituras = ref([])
const filters = ref({ role: '', prefeitura_id: '', dataInicial: '', dataFinal: '' })
const stats = ref({ total: 0, cidadaos: 0, admins: 0, novos: 0 })
const roleChart = ref(null)

const columns = [
  { key: 'id', label: 'ID', sortable: true },
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'role', label: 'Role', sortable: true },
  { key: 'created_at', label: 'Cadastro', sortable: true }
]

onMounted(async () => {
  await loadPrefeituras()
  await loadData()
})

const loadPrefeituras = async () => {
  try {
    prefeituras.value = await adminApi.listPrefeituras()
  } catch (err) {
    console.error(err)
  }
}

const loadData = async () => {
  loading.value = true
  try {
    const data = await adminApi.getUsersReport(filters.value)
    usuarios.value = data.usuarios || []
    stats.value = data.stats || { total: 0, cidadaos: 0, admins: 0, novos: 0 }
    setTimeout(() => createChart(), 100)
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const createChart = () => {
  if (roleChart.value) {
    new Chart(roleChart.value, {
      type: 'pie',
      data: {
        labels: ['Cidadãos', 'Admins', 'Prefeituras', 'Super'],
        datasets: [{
          data: [stats.value.cidadaos, stats.value.admins, 5, 2],
          backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444']
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
      }
    })
  }
}

const clearFilters = () => {
  filters.value = { role: '', prefeitura_id: '', dataInicial: '', dataFinal: '' }
  loadData()
}

const exportar = async () => {
  exporting.value = true
  try {
    const csvData = await adminApi.exportUsuariosCSV(filters.value)
    const blob = new Blob([csvData], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `relatorio-usuarios-${new Date().toISOString().split('T')[0]}.csv`
    a.click()
    window.URL.revokeObjectURL(url)
  } catch (err) {
    alert('Erro ao exportar')
  } finally {
    exporting.value = false
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('pt-BR')
}
</script>

<style scoped>
.relatorio-usuarios {
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

.chart-section {
  margin-bottom: 2rem;
}

.chart-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  max-width: 500px;
  margin: 0 auto;
}

.chart-card h3 {
  margin: 0 0 1rem;
  font-size: 1.125rem;
  color: #111827;
  text-align: center;
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

.role-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
}

.role-cidadao {
  background-color: #dbeafe;
  color: #1e40af;
}

.role-admin {
  background-color: #d1fae5;
  color: #065f46;
}

.role-prefeitura {
  background-color: #fef3c7;
  color: #92400e;
}

.role-super {
  background-color: #fee2e2;
  color: #991b1b;
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
</style>
