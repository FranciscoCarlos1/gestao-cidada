<template>
  <div class="super-dashboard">
    <div class="page-header">
      <h1>Dashboard Super Admin</h1>
      <p>Visão geral completa do sistema</p>
    </div>

    <!-- Estatísticas Gerais -->
    <div class="stats-grid">
      <StatCard 
        label="Total de Usuários" 
        :value="stats.totalUsers" 
        color="blue"
        subtitle="Todos os tipos"
      >
        <template #icon>👥</template>
      </StatCard>
      
      <StatCard 
        label="Prefeituras" 
        :value="stats.totalPrefeituras" 
        color="green"
        subtitle="Ativas no sistema"
      >
        <template #icon>🏛️</template>
      </StatCard>
      
      <StatCard 
        label="Problemas" 
        :value="stats.totalProblemas" 
        color="purple"
        subtitle="Total registrado"
      >
        <template #icon>📋</template>
      </StatCard>
      
      <StatCard 
        label="Pendentes" 
        :value="stats.problemasPendentes" 
        color="yellow"
        subtitle="Aguardando atenção"
      >
        <template #icon>⏳</template>
      </StatCard>

      <StatCard 
        label="Em Andamento" 
        :value="stats.problemasAndamento" 
        color="blue"
        subtitle="Sendo resolvidos"
      >
        <template #icon>🔄</template>
      </StatCard>
      
      <StatCard 
        label="Resolvidos" 
        :value="stats.problemasResolvidos" 
        color="green"
        subtitle="Finalizados"
      >
        <template #icon>✅</template>
      </StatCard>
    </div>

    <!-- Gráficos -->
    <div class="charts-grid">
      <div class="chart-card">
        <h3>Problemas por Status</h3>
        <canvas ref="statusChart"></canvas>
      </div>
      
      <div class="chart-card">
        <h3>Usuários por Role</h3>
        <canvas ref="rolesChart"></canvas>
      </div>
      
      <div class="chart-card full-width">
        <h3>Problemas por Mês (Últimos 6 meses)</h3>
        <canvas ref="timelineChart"></canvas>
      </div>
    </div>

    <!-- Atividades Recentes -->
    <div class="recent-activity">
      <h3>Atividades Recentes</h3>
      <div v-if="loadingActivity" class="loading">Carregando...</div>
      <div v-else-if="!recentActivities.length" class="no-data">Nenhuma atividade recente</div>
      <div v-else class="activity-list">
        <div v-for="activity in recentActivities" :key="activity.id" class="activity-item">
          <div class="activity-icon" :class="`activity-${activity.type}`">
            {{ getActivityIcon(activity.type) }}
          </div>
          <div class="activity-content">
            <p class="activity-title">{{ activity.description }}</p>
            <p class="activity-meta">
              <span>{{ activity.user }}</span> • 
              <span>{{ formatDate(activity.created_at) }}</span>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="quick-actions">
      <h3>Ações Rápidas</h3>
      <div class="actions-grid">
        <button @click="$router.push('/super/usuarios')" class="action-btn">
          <span class="action-icon">👤</span>
          <span>Gerenciar Usuários</span>
        </button>
        <button @click="$router.push('/super/prefeituras')" class="action-btn">
          <span class="action-icon">🏛️</span>
          <span>Gerenciar Prefeituras</span>
        </button>
        <button @click="$router.push('/super/roles')" class="action-btn">
          <span class="action-icon">🔐</span>
          <span>Roles & Permissões</span>
        </button>
        <button @click="$router.push('/relatorios/auditoria')" class="action-btn">
          <span class="action-icon">📊</span>
          <span>Ver Auditoria</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Chart, registerables } from 'chart.js'
import StatCard from '../components/StatCard.vue'
import * as adminApi from '../lib/api'

Chart.register(...registerables)

const stats = ref({
  totalUsers: 0,
  totalPrefeituras: 0,
  totalProblemas: 0,
  problemasPendentes: 0,
  problemasAndamento: 0,
  problemasResolvidos: 0
})

const recentActivities = ref([])
const loadingActivity = ref(true)

const statusChart = ref(null)
const rolesChart = ref(null)
const timelineChart = ref(null)

let chartInstances = []

onMounted(async () => {
  await loadStats()
  await loadRecentActivities()
  createCharts()
})

const loadStats = async () => {
  try {
    const data = await adminApi.getDashboardStats()
    stats.value = data
  } catch (error) {
    console.error('Erro ao carregar estatísticas:', error)
  }
}

const loadRecentActivities = async () => {
  loadingActivity.value = true
  try {
    const data = await adminApi.getRecentActivities()
    recentActivities.value = data.slice(0, 10)
  } catch (error) {
    console.error('Erro ao carregar atividades:', error)
    recentActivities.value = []
  } finally {
    loadingActivity.value = false
  }
}

const createCharts = () => {
  // Gráfico de Status
  if (statusChart.value) {
    chartInstances.push(new Chart(statusChart.value, {
      type: 'doughnut',
      data: {
        labels: ['Pendente', 'Em Andamento', 'Resolvido'],
        datasets: [{
          data: [
            stats.value.problemasPendentes,
            stats.value.problemasAndamento,
            stats.value.problemasResolvidos
          ],
          backgroundColor: ['#fbbf24', '#3b82f6', '#10b981']
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

  // Gráfico de Roles
  if (rolesChart.value) {
    chartInstances.push(new Chart(rolesChart.value, {
      type: 'pie',
      data: {
        labels: ['Cidadãos', 'Admins', 'Prefeituras', 'Super'],
        datasets: [{
          data: [120, 15, 8, 2], // Exemplo, deveria vir da API
          backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444']
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

  // Gráfico de Timeline
  if (timelineChart.value) {
    chartInstances.push(new Chart(timelineChart.value, {
      type: 'line',
      data: {
        labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
        datasets: [{
          label: 'Problemas Criados',
          data: [12, 19, 15, 25, 22, 30], // Exemplo, deveria vir da API
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          tension: 0.4
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

const getActivityIcon = (type) => {
  const icons = {
    create: '➕',
    update: '✏️',
    delete: '🗑️',
    login: '🔑',
    status_change: '🔄'
  }
  return icons[type] || '📝'
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<style scoped>
.super-dashboard {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
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

.chart-card.full-width {
  grid-column: 1 / -1;
}

.chart-card h3 {
  margin: 0 0 1rem;
  font-size: 1.125rem;
  color: #111827;
}

.recent-activity {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.recent-activity h3 {
  margin: 0 0 1rem;
  font-size: 1.125rem;
  color: #111827;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.activity-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 8px;
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.activity-create { background-color: #dbeafe; }
.activity-update { background-color: #fef3c7; }
.activity-delete { background-color: #fee2e2; }
.activity-login { background-color: #d1fae5; }
.activity-status_change { background-color: #ede9fe; }

.activity-content {
  flex: 1;
  min-width: 0;
}

.activity-title {
  margin: 0 0 0.25rem;
  font-size: 0.875rem;
  color: #111827;
  font-weight: 500;
}

.activity-meta {
  margin: 0;
  font-size: 0.75rem;
  color: #6b7280;
}

.quick-actions {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.quick-actions h3 {
  margin: 0 0 1rem;
  font-size: 1.125rem;
  color: #111827;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1.5rem;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
}

.action-btn:hover {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.action-icon {
  font-size: 2rem;
}

.loading,
.no-data {
  text-align: center;
  padding: 2rem;
  color: #6b7280;
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
