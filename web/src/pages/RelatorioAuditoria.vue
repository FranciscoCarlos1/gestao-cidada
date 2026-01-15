<template>
  <div class="relatorio-auditoria">
    <div class="page-header">
      <div>
        <h1>Relatório de Auditoria</h1>
        <p>Logs de atividades do sistema</p>
      </div>
      <button @click="exportar" class="btn btn-primary" :disabled="exporting">
        {{ exporting ? 'Exportando...' : '📥 Exportar' }}
      </button>
    </div>

    <FilterBar @clear="clearFilters" @apply="loadData">
      <div class="filter-field">
        <label>Usuário</label>
        <input v-model="filters.user_id" type="text" placeholder="ID do usuário" />
      </div>
      <div class="filter-field">
        <label>Tabela</label>
        <select v-model="filters.table_name">
          <option value="">Todas</option>
          <option value="users">users</option>
          <option value="problemas">problemas</option>
          <option value="prefeituras">prefeituras</option>
          <option value="roles">roles</option>
        </select>
      </div>
      <div class="filter-field">
        <label>Ação</label>
        <select v-model="filters.action">
          <option value="">Todas</option>
          <option value="create">Create</option>
          <option value="update">Update</option>
          <option value="delete">Delete</option>
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

    <DataTable
      :columns="columns"
      :data="logs"
      :loading="loading"
      :pagination="true"
      :per-page="20"
    >
      <template #cell-action="{ value }">
        <span class="action-badge" :class="`action-${value}`">{{ value }}</span>
      </template>

      <template #cell-created_at="{ value }">
        {{ formatDateTime(value) }}
      </template>

      <template #actions="{ row }">
        <button @click="verDetalhes(row)" class="btn-icon" title="Ver mudanças">📝</button>
      </template>
    </DataTable>

    <Modal 
      :visible="showModal" 
      title="Detalhes da Mudança"
      size="large"
      @close="closeModal"
    >
      <div v-if="selectedLog" class="log-details">
        <div class="detail-section">
          <h4>Informações</h4>
          <p><strong>Usuário:</strong> {{ selectedLog.user_name || selectedLog.user_id }}</p>
          <p><strong>Ação:</strong> {{ selectedLog.action }}</p>
          <p><strong>Tabela:</strong> {{ selectedLog.table_name }}</p>
          <p><strong>Registro ID:</strong> {{ selectedLog.record_id }}</p>
          <p><strong>Data:</strong> {{ formatDateTime(selectedLog.created_at) }}</p>
        </div>

        <div v-if="selectedLog.old_values" class="detail-section">
          <h4>Valores Antigos</h4>
          <pre>{{ JSON.stringify(selectedLog.old_values, null, 2) }}</pre>
        </div>

        <div v-if="selectedLog.new_values" class="detail-section">
          <h4>Valores Novos</h4>
          <pre>{{ JSON.stringify(selectedLog.new_values, null, 2) }}</pre>
        </div>
      </div>

      <template #footer>
        <button @click="closeModal" class="btn btn-secondary">Fechar</button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import DataTable from '../components/DataTable.vue'
import FilterBar from '../components/FilterBar.vue'
import Modal from '../components/Modal.vue'
import * as adminApi from '../lib/api'

const loading = ref(false)
const exporting = ref(false)
const logs = ref([])
const showModal = ref(false)
const selectedLog = ref(null)

const filters = ref({
  user_id: '',
  table_name: '',
  action: '',
  dataInicial: '',
  dataFinal: ''
})

const columns = [
  { key: 'id', label: 'ID', sortable: true },
  { key: 'user_name', label: 'Usuário', sortable: true },
  { key: 'action', label: 'Ação', sortable: true },
  { key: 'table_name', label: 'Tabela', sortable: true },
  { key: 'record_id', label: 'Registro ID', sortable: true },
  { key: 'created_at', label: 'Data/Hora', sortable: true }
]

onMounted(() => {
  loadData()
})

const loadData = async () => {
  loading.value = true
  try {
    logs.value = await adminApi.getAuditLogs(filters.value)
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const clearFilters = () => {
  filters.value = { user_id: '', table_name: '', action: '', dataInicial: '', dataFinal: '' }
  loadData()
}

const verDetalhes = (log) => {
  selectedLog.value = log
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedLog.value = null
}

const exportar = async () => {
  exporting.value = true
  try {
    const csvData = await adminApi.exportAuditLogsCSV(filters.value)
    const blob = new Blob([csvData], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `auditoria-${new Date().toISOString().split('T')[0]}.csv`
    a.click()
    window.URL.revokeObjectURL(url)
  } catch (err) {
    alert('Erro ao exportar')
  } finally {
    exporting.value = false
  }
}

const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('pt-BR')
}
</script>

<style scoped>
.relatorio-auditoria {
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

.action-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: uppercase;
}

.action-create {
  background-color: #d1fae5;
  color: #065f46;
}

.action-update {
  background-color: #fef3c7;
  color: #92400e;
}

.action-delete {
  background-color: #fee2e2;
  color: #991b1b;
}

.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.25rem;
  padding: 0.25rem;
}

.log-details {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.detail-section h4 {
  margin: 0 0 0.75rem;
  font-size: 1rem;
  color: #111827;
}

.detail-section p {
  margin: 0.5rem 0;
  font-size: 0.875rem;
  color: #374151;
}

.detail-section pre {
  background: #f3f4f6;
  padding: 1rem;
  border-radius: 6px;
  overflow-x: auto;
  font-size: 0.75rem;
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

.btn-secondary {
  background-color: #e5e7eb;
  color: #374151;
}

.btn-secondary:hover {
  background-color: #d1d5db;
}
</style>
