<template>
  <div class="data-table-container">
    <div v-if="loading" class="table-loading">
      <div class="spinner"></div>
      <p>Carregando...</p>
    </div>
    
    <div v-else>
      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th 
                v-for="col in columns" 
                :key="col.key"
                :class="{ sortable: col.sortable }"
                @click="col.sortable && handleSort(col.key)"
              >
                {{ col.label }}
                <span v-if="col.sortable && sortKey === col.key" class="sort-icon">
                  {{ sortOrder === 'asc' ? '↑' : '↓' }}
                </span>
              </th>
              <th v-if="$slots.actions" class="actions-column">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!sortedData.length">
              <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="no-data">
                {{ emptyMessage }}
              </td>
            </tr>
            <tr v-for="(row, index) in paginatedData" :key="index">
              <td v-for="col in columns" :key="col.key">
                <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                  {{ formatValue(row[col.key], col) }}
                </slot>
              </td>
              <td v-if="$slots.actions" class="actions-cell">
                <slot name="actions" :row="row"></slot>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="pagination && totalPages > 1" class="table-pagination">
        <div class="pagination-info">
          Mostrando {{ startIndex + 1 }} a {{ endIndex }} de {{ sortedData.length }} registros
        </div>
        <div class="pagination-controls">
          <button 
            @click="goToPage(currentPage - 1)" 
            :disabled="currentPage === 1"
            class="pagination-btn"
          >
            Anterior
          </button>
          <button 
            v-for="page in visiblePages" 
            :key="page"
            @click="goToPage(page)"
            :class="['pagination-btn', { active: page === currentPage }]"
          >
            {{ page }}
          </button>
          <button 
            @click="goToPage(currentPage + 1)" 
            :disabled="currentPage === totalPages"
            class="pagination-btn"
          >
            Próxima
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  columns: { type: Array, required: true }, // [{ key: 'name', label: 'Nome', sortable: true, format: (val) => val }]
  data: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  pagination: { type: Boolean, default: true },
  perPage: { type: Number, default: 10 },
  emptyMessage: { type: String, default: 'Nenhum registro encontrado' }
})

const currentPage = ref(1)
const sortKey = ref('')
const sortOrder = ref('asc')

const sortedData = computed(() => {
  if (!sortKey.value) return props.data
  
  return [...props.data].sort((a, b) => {
    const aVal = a[sortKey.value]
    const bVal = b[sortKey.value]
    
    if (aVal === bVal) return 0
    
    const comparison = aVal < bVal ? -1 : 1
    return sortOrder.value === 'asc' ? comparison : -comparison
  })
})

const totalPages = computed(() => Math.ceil(sortedData.value.length / props.perPage))
const startIndex = computed(() => (currentPage.value - 1) * props.perPage)
const endIndex = computed(() => Math.min(startIndex.value + props.perPage, sortedData.value.length))

const paginatedData = computed(() => {
  if (!props.pagination) return sortedData.value
  return sortedData.value.slice(startIndex.value, endIndex.value)
})

const visiblePages = computed(() => {
  const pages = []
  const range = 2
  const start = Math.max(1, currentPage.value - range)
  const end = Math.min(totalPages.value, currentPage.value + range)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
})

const handleSort = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
  currentPage.value = 1
}

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}

const formatValue = (value, col) => {
  if (col.format && typeof col.format === 'function') {
    return col.format(value)
  }
  if (value === null || value === undefined) return '-'
  return value
}
</script>

<style scoped>
.data-table-container {
  width: 100%;
}

.table-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
}

.spinner {
  border: 3px solid #f3f4f6;
  border-top: 3px solid #3b82f6;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.table-wrapper {
  overflow-x: auto;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
}

.data-table thead {
  background-color: #f9fafb;
}

.data-table th {
  padding: 0.75rem 1rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.875rem;
  color: #374151;
  border-bottom: 1px solid #e5e7eb;
  white-space: nowrap;
}

.data-table th.sortable {
  cursor: pointer;
  user-select: none;
}

.data-table th.sortable:hover {
  background-color: #f3f4f6;
}

.sort-icon {
  margin-left: 0.25rem;
  color: #3b82f6;
}

.data-table td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #e5e7eb;
  font-size: 0.875rem;
  color: #1f2937;
}

.data-table tbody tr:hover {
  background-color: #f9fafb;
}

.no-data {
  text-align: center;
  color: #6b7280;
  padding: 2rem !important;
}

.actions-column {
  text-align: center;
  width: 150px;
}

.actions-cell {
  text-align: center;
}

.table-pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-top: none;
  border-radius: 0 0 8px 8px;
}

.pagination-info {
  font-size: 0.875rem;
  color: #6b7280;
}

.pagination-controls {
  display: flex;
  gap: 0.5rem;
}

.pagination-btn {
  padding: 0.5rem 0.75rem;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.875rem;
  color: #374151;
  transition: all 0.2s;
}

.pagination-btn:hover:not(:disabled) {
  background-color: #f3f4f6;
  border-color: #9ca3af;
}

.pagination-btn.active {
  background-color: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
