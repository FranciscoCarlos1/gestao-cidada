<template>
  <div class="filter-bar">
    <div class="filter-header">
      <h3 class="filter-title">Filtros</h3>
      <button v-if="showClear" @click="clearFilters" class="btn-clear">Limpar</button>
    </div>
    <div class="filter-content">
      <slot></slot>
    </div>
    <div v-if="showApply" class="filter-actions">
      <button @click="applyFilters" class="btn btn-primary">Aplicar Filtros</button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  showClear: { type: Boolean, default: true },
  showApply: { type: Boolean, default: true }
})

const emit = defineEmits(['clear', 'apply'])

const clearFilters = () => {
  emit('clear')
}

const applyFilters = () => {
  emit('apply')
}
</script>

<style scoped>
.filter-bar {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 1.25rem;
  margin-bottom: 1.5rem;
}

.filter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.filter-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
}

.btn-clear {
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  font-size: 0.875rem;
  text-decoration: underline;
  padding: 0;
}

.btn-clear:hover {
  color: #374151;
}

.filter-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.filter-actions {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
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

.btn-primary:hover {
  background-color: #2563eb;
}
</style>
