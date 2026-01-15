<template>
  <div class="super-usuarios">
    <div class="page-header">
      <div>
        <h1>Gerenciamento de Usuários</h1>
        <p>Gerencie todos os usuários do sistema</p>
      </div>
      <button @click="openCreateModal" class="btn btn-primary">
        ➕ Novo Usuário
      </button>
    </div>

    <!-- Filtros -->
    <FilterBar @clear="clearFilters" @apply="applyFilters">
      <div class="filter-field">
        <label>Buscar</label>
        <input v-model="filters.search" type="text" placeholder="Nome ou email..." />
      </div>
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
        <label>Status</label>
        <select v-model="filters.status">
          <option value="">Todos</option>
          <option value="ativo">Ativo</option>
          <option value="inativo">Inativo</option>
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

    <!-- Tabela -->
    <DataTable
      :columns="columns"
      :data="filteredUsers"
      :loading="loading"
      :pagination="true"
      :per-page="15"
    >
      <template #cell-status="{ value }">
        <span class="badge" :class="`badge-${value}`">
          {{ value === 'ativo' ? 'Ativo' : 'Inativo' }}
        </span>
      </template>
      
      <template #cell-role="{ value }">
        <span class="role-badge" :class="`role-${value}`">
          {{ getRoleLabel(value) }}
        </span>
      </template>

      <template #cell-created_at="{ value }">
        {{ formatDate(value) }}
      </template>

      <template #actions="{ row }">
        <div class="action-buttons">
          <button @click="editUser(row)" class="btn-icon" title="Editar">✏️</button>
          <button 
            @click="toggleStatus(row)" 
            class="btn-icon" 
            :title="row.status === 'ativo' ? 'Desativar' : 'Ativar'"
          >
            {{ row.status === 'ativo' ? '🔒' : '🔓' }}
          </button>
          <button @click="deleteUser(row)" class="btn-icon btn-danger" title="Deletar">🗑️</button>
        </div>
      </template>
    </DataTable>

    <!-- Modal de Criar/Editar -->
    <Modal 
      :visible="showModal" 
      :title="isEditing ? 'Editar Usuário' : 'Novo Usuário'"
      size="medium"
      @close="closeModal"
    >
      <form @submit.prevent="saveUser" class="user-form">
        <div class="form-group">
          <label>Nome *</label>
          <input v-model="form.name" type="text" required />
        </div>

        <div class="form-group">
          <label>Email *</label>
          <input v-model="form.email" type="email" required />
        </div>

        <div class="form-group" v-if="!isEditing">
          <label>Senha *</label>
          <input v-model="form.password" type="password" required minlength="6" />
        </div>

        <div class="form-group">
          <label>Role *</label>
          <select v-model="form.role" required>
            <option value="">Selecione...</option>
            <option value="cidadao">Cidadão</option>
            <option value="admin">Admin</option>
            <option value="prefeitura">Prefeitura</option>
            <option value="super">Super Admin</option>
          </select>
        </div>

        <div class="form-group" v-if="form.role === 'admin' || form.role === 'prefeitura'">
          <label>Prefeitura *</label>
          <select v-model="form.prefeitura_id" required>
            <option value="">Selecione...</option>
            <option v-for="pref in prefeituras" :key="pref.id" :value="pref.id">
              {{ pref.nome }}
            </option>
          </select>
        </div>

        <div class="form-group">
          <label>Status *</label>
          <select v-model="form.status" required>
            <option value="ativo">Ativo</option>
            <option value="inativo">Inativo</option>
          </select>
        </div>

        <div v-if="error" class="error-message">{{ error }}</div>
        <div v-if="success" class="success-message">{{ success }}</div>
      </form>

      <template #footer>
        <button @click="closeModal" class="btn btn-secondary" type="button">Cancelar</button>
        <button @click="saveUser" class="btn btn-primary" :disabled="saving">
          {{ saving ? 'Salvando...' : 'Salvar' }}
        </button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import DataTable from '../components/DataTable.vue'
import Modal from '../components/Modal.vue'
import FilterBar from '../components/FilterBar.vue'
import * as adminApi from '../lib/api'

const loading = ref(false)
const saving = ref(false)
const users = ref([])
const prefeituras = ref([])

const showModal = ref(false)
const isEditing = ref(false)
const error = ref('')
const success = ref('')

const filters = ref({
  search: '',
  role: '',
  status: '',
  prefeitura_id: ''
})

const form = ref({
  id: null,
  name: '',
  email: '',
  password: '',
  role: '',
  prefeitura_id: '',
  status: 'ativo'
})

const columns = [
  { key: 'id', label: 'ID', sortable: true },
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'role', label: 'Role', sortable: true },
  { key: 'status', label: 'Status', sortable: true },
  { key: 'created_at', label: 'Cadastro', sortable: true }
]

const filteredUsers = computed(() => {
  let result = users.value

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    result = result.filter(u => 
      u.name?.toLowerCase().includes(search) || 
      u.email?.toLowerCase().includes(search)
    )
  }

  if (filters.value.role) {
    result = result.filter(u => u.role === filters.value.role)
  }

  if (filters.value.status) {
    result = result.filter(u => u.status === filters.value.status)
  }

  if (filters.value.prefeitura_id) {
    result = result.filter(u => u.prefeitura_id == filters.value.prefeitura_id)
  }

  return result
})

onMounted(async () => {
  await loadUsers()
  await loadPrefeituras()
})

const loadUsers = async () => {
  loading.value = true
  try {
    users.value = await adminApi.listUsers()
  } catch (err) {
    console.error('Erro ao carregar usuários:', err)
  } finally {
    loading.value = false
  }
}

const loadPrefeituras = async () => {
  try {
    prefeituras.value = await adminApi.listPrefeituras()
  } catch (err) {
    console.error('Erro ao carregar prefeituras:', err)
  }
}

const openCreateModal = () => {
  isEditing.value = false
  form.value = {
    id: null,
    name: '',
    email: '',
    password: '',
    role: '',
    prefeitura_id: '',
    status: 'ativo'
  }
  error.value = ''
  success.value = ''
  showModal.value = true
}

const editUser = (user) => {
  isEditing.value = true
  form.value = {
    id: user.id,
    name: user.name,
    email: user.email,
    password: '',
    role: user.role,
    prefeitura_id: user.prefeitura_id || '',
    status: user.status || 'ativo'
  }
  error.value = ''
  success.value = ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  error.value = ''
  success.value = ''
}

const saveUser = async () => {
  saving.value = true
  error.value = ''
  success.value = ''

  try {
    if (isEditing.value) {
      await adminApi.updateUser(form.value.id, form.value)
      success.value = 'Usuário atualizado com sucesso!'
    } else {
      await adminApi.createUser(form.value)
      success.value = 'Usuário criado com sucesso!'
    }
    
    setTimeout(async () => {
      await loadUsers()
      closeModal()
    }, 1500)
  } catch (err) {
    error.value = err.response?.data?.message || 'Erro ao salvar usuário'
  } finally {
    saving.value = false
  }
}

const toggleStatus = async (user) => {
  if (!confirm(`Deseja ${user.status === 'ativo' ? 'desativar' : 'ativar'} este usuário?`)) return

  try {
    const newStatus = user.status === 'ativo' ? 'inativo' : 'ativo'
    await adminApi.updateUserStatus(user.id, newStatus)
    await loadUsers()
  } catch (err) {
    alert('Erro ao alterar status')
  }
}

const deleteUser = async (user) => {
  if (!confirm(`Tem certeza que deseja deletar o usuário ${user.name}?`)) return

  try {
    await adminApi.deleteUser(user.id)
    await loadUsers()
  } catch (err) {
    alert('Erro ao deletar usuário')
  }
}

const clearFilters = () => {
  filters.value = {
    search: '',
    role: '',
    status: '',
    prefeitura_id: ''
  }
}

const applyFilters = () => {
  // Filtros são reativos, então aplicação é automática
}

const getRoleLabel = (role) => {
  const labels = {
    cidadao: 'Cidadão',
    admin: 'Admin',
    prefeitura: 'Prefeitura',
    super: 'Super Admin'
  }
  return labels[role] || role
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('pt-BR')
}
</script>

<style scoped>
.super-usuarios {
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

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
}

.badge-ativo {
  background-color: #d1fae5;
  color: #065f46;
}

.badge-inativo {
  background-color: #fee2e2;
  color: #991b1b;
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
  background-color: #fef3c7;
  color: #92400e;
}

.role-prefeitura {
  background-color: #ede9fe;
  color: #5b21b6;
}

.role-super {
  background-color: #fee2e2;
  color: #991b1b;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
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

.btn-icon.btn-danger:hover {
  filter: brightness(1.2);
}

.user-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
}

.form-group input,
.form-group select {
  padding: 0.625rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.error-message {
  padding: 0.75rem;
  background-color: #fee2e2;
  color: #991b1b;
  border-radius: 6px;
  font-size: 0.875rem;
}

.success-message {
  padding: 0.75rem;
  background-color: #d1fae5;
  color: #065f46;
  border-radius: 6px;
  font-size: 0.875rem;
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
