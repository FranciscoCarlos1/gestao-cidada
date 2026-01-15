<template>
  <div class="stat-card" :class="colorClass">
    <div class="stat-icon">
      <slot name="icon">📊</slot>
    </div>
    <div class="stat-content">
      <p class="stat-label">{{ label }}</p>
      <h3 class="stat-value">{{ formattedValue }}</h3>
      <p v-if="subtitle" class="stat-subtitle">{{ subtitle }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: { type: String, required: true },
  value: { type: [Number, String], required: true },
  subtitle: { type: String, default: '' },
  color: { type: String, default: 'blue' }, // blue, green, red, yellow, purple
  format: { type: Function, default: null }
})

const colorClass = computed(() => `color-${props.color}`)

const formattedValue = computed(() => {
  if (props.format) return props.format(props.value)
  return props.value
})
</script>

<style scoped>
.stat-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.75rem;
  flex-shrink: 0;
}

.color-blue .stat-icon {
  background-color: #dbeafe;
  color: #1e40af;
}

.color-green .stat-icon {
  background-color: #d1fae5;
  color: #065f46;
}

.color-red .stat-icon {
  background-color: #fee2e2;
  color: #991b1b;
}

.color-yellow .stat-icon {
  background-color: #fef3c7;
  color: #92400e;
}

.color-purple .stat-icon {
  background-color: #ede9fe;
  color: #5b21b6;
}

.stat-content {
  flex: 1;
  min-width: 0;
}

.stat-label {
  margin: 0 0 0.25rem;
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.stat-value {
  margin: 0 0 0.25rem;
  font-size: 1.875rem;
  font-weight: 700;
  color: #111827;
  line-height: 1;
}

.stat-subtitle {
  margin: 0;
  font-size: 0.75rem;
  color: #9ca3af;
}
</style>
