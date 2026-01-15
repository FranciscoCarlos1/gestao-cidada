<template>
  <div class="super-roles">
    <div class="page-header">
      <div>
        <h1>Roles & Permissões</h1>
        <p>Gerencie roles e suas permissões</p>
      </div>
      <button @click="openCreateModal" class="btn btn-primary">
        ➕ Nova Role
      </button>
    </div>

    <!-- Lista de Roles -->
    <DataTable
      :columns="columns"
      :data="roles"
      :loading="loading"
      :pagination="true"
      :per-page="10"
    >
      <template #cell-permissions="{ row }">
        <span class="permissions-count">
          {{ row.permissions?.length || 0 }} permissões
        </span>
      </template>

      <template #cell-users_count="{ value }">
        <span class="badge badge-blue">{{ value || 0 }} usuários</span>
      </template>

      <template #actions="{ row }">
        <div class="action-buttons">
          <button @click="editRole(row)" class="btn-icon" title="Editar">✏️</button>
          <button @click="managePermissions(row)" class="btn-icon" title="Gerenciar Permissões">🔐</button>
          <button @click="viewUsers(row)" class="btn-icon" title="Ver Usuários">👥</button>
          <button @click="deleteRole(row)" class="btn-icon btn-danger" title="Deletar">🗑️</button>
        </div>
      </template>
    </DataTable>

    <!-- Modal de Criar/Editar Role -->
    <Modal 
      :visible="showModal" 
      :title="isEditing ? 'Editar Role' : 'Nova Role'"
      size="medium"
      @close="closeModal"
    >
      <form @submit.prevent="saveRole" class="role-form">
        <div class="form-group">
          <label>Nome *</label>
          <input v-model="form.name" type="text" required placeholder="Ex: moderador" />
        </div>

        <div class="form-group">
          <label>Descrição</label>
          <textarea v-model="form.description" rows="3" placeholder="Descrição da role..."></textarea>
        </div>

        <div v-if="error" class="error-message">{{ error }}</div>
        <div v-if="success" class="success-message">{{ success }}</div>
      </form>

      <template #footer>
        <button @click="closeModal" class="btn btn-secondary" type="button">Cancelar</button>
        <button @click="saveRole" class="btn btn-primary" :disabled="saving">
          {{ saving ? 'Salvando...' : 'Salvar' }}
        </button>
      </template>
    </Modal>

    <!-- Modal de Gerenciar Permissões -->
    <Modal 
      :visible="showPermissionsModal" 
      title="Gerenciar Permissões"
      size="large"
      @close="closePermissionsModal"
    >
      <div class="permissions-manager">
        <div class="permissions-header">
          <h3>{{ selectedRole?.name }}</h3>
          <p>{{ selectedRole?.description }}</p>
        </div>

        <div v-if="loadingPermissions" class="loading">Carregando permissões...</div>
        <div v-else class="permissions-list">
          <div v-for="perm in allPermissions" :key="perm.id" class="permission-item">
            <label class="checkbox-label">
              <input 
                type="checkbox" 
                :checked="isPermissionSelected(perm.id)"
                @change="togglePermission(perm.id)"
              />
              <div>
                <strong>{{ perm.name }}</strong>
                <p>{{ perm.description }}</p>
              </div>
            </label>
          </div>
        </div>
      </div>

      <template #footer>
        <button @click="closePermissionsModal" class="btn btn-secondary" type="button">Cancelar</button>
        <button @click="savePermissions" class="btn btn-primary" :disabled="savingPermissions">
          {{ savingPermissions ? 'Salvando...' : 'Salvar Permissões' }}
        </button>
      </template>
    </Modal>

    <!-- Modal de Ver Usuários -->
    <Modal 
      :visible="showUsersModal" 
      :title="`Usuários com role: ${selectedRole?.name}`"
      size="large"
      @close="closeUsersModal"
    >
      <div v-if="loadingUsers" class="loading">Carregando usuários...</div>
      <div v-else-if="!roleUsers.length" class="no-data">Nenhum usuário com esta role</div>
      <div v-else class="users-list">
        <div v-for="user in roleUsers" :key="user.id" class="user-item">
          <div class="user-info">
            <strong>{{ user.name }}</strong>
            <span>{{ user.email }}</span>
          </div>
          <span class="badge" :class="`badge-${user.status}`">
            {{ user.status === 'ativo' ? 'Ativo' : 'Inativo' }}
          </span>
        </div>
      </div>

      <template #footer>
        <button @click="closeUsersModal" class="btn btn-secondary" type="button">Fechar</button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import DataTable from '../components/DataTable.vue'
import Modal from '../components/Modal.vue'
import * as adminApi from '../lib/api'

const loading = ref(false)
const saving = ref(false)
const savingPermissions = ref(false)
const loadingPermissions = ref(false)
const loadingUsers = ref(false)

const roles = ref([])
const allPermissions = ref([])
const selectedPermissions = ref([])
const roleUsers = ref([])

const showModal = ref(false)
const showPermissionsModal = ref(false)
const showUsersModal = ref(false)
const isEditing = ref(false)
const selectedRole = ref(null)
const error = ref('')
const success = ref('')

const form = ref({
  id: null,
  name: '',
  description: ''
})

const columns = [
  { key: 'id', label: 'ID', sortable: true },
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'description', label: 'Descrição', sortable: true },
  { key: 'permissions', label: 'Permissões' },
  { key: 'users_count', label: 'Usuários' }
]

onMounted(async () => {
  await loadRoles()
  await loadAllPermissions()
})

const loadRoles = async () => {
  loading.value = true
  try {
    roles.value = await adminApi.listRoles()
  } catch (err) {
    console.error('Erro ao carregar roles:', err)
  } finally {
    loading.value = false
  }
}

const loadAllPermissions = async () => {
  try {
    allPermissions.value = await adminApi.listPermissions()
  } catch (err) {
    console.error('Erro ao carregar permissões:', err)
  }
}

const openCreateModal = () => {
  isEditing.value = false
  form.value = { id: null, name: '', description: '' }
  error.value = ''
  success.value = ''
  showModal.value = true
}

const editRole = (role) => {
  isEditing.value = true
  form.value = {
    id: role.id,
    name: role.name,
    description: role.description || ''
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

const saveRole = async () => {
  saving.value = true
  error.value = ''
  success.value = ''

  try {
    if (isEditing.value) {
      await adminApi.updateRole(form.value.id, form.value)
      success.value = 'Role atualizada com sucesso!'
    } else {
      await adminApi.createRole(form.value)
      success.value = 'Role criada com sucesso!'
    }
    
    setTimeout(async () => {
      await loadRoles()
      closeModal()
    }, 1500)
  } catch (err) {
    error.value = err.response?.data?.message || 'Erro ao salvar role'
  } finally {
    saving.value = false
  }
}

const managePermissions = async (role) => {
  selectedRole.value = role
  loadingPermissions.value = true
  showPermissionsModal.value = true
  
  try {
    const roleData = await adminApi.getRole(role.id)
    selectedPermissions.value = roleData.permissions?.map(p => p.id) || []
  } catch (err) {
    console.error('Erro ao carregar permissões da role:', err)
    selectedPermissions.value = []
  } finally {
    loadingPermissions.value = false
  }
}

const closePermissionsModal = () => {
  showPermissionsModal.value = false
  selectedRole.value = null
  selectedPermissions.value = []
}

const isPermissionSelected = (permId) => {
  return selectedPermissions.value.includes(permId)
}

const togglePermission = (permId) => {
  const index = selectedPermissions.value.indexOf(permId)
  if (index > -1) {
    selectedPermissions.value.splice(index, 1)
  } else {
    selectedPermissions.value.push(permId)
  }
}

const savePermissions = async () => {
  savingPermissions.value = true
  
  try {
    await adminApi.assignPermissions(selectedRole.value.id, selectedPermissions.value)
    await loadRoles()
    closePermissionsModal()
  } catch (err) {
    alert('Erro ao salvar permissões')
  } finally {
    savingPermissions.value = false
  }
}

const viewUsers = async (role) => {
  selectedRole.value = role
  loadingUsers.value = true
  showUsersModal.value = true
  
  try {
    roleUsers.value = await adminApi.getRoleUsers(role.id)
  } catch (err) {
    console.error('Erro ao carregar usuários:', err)
    roleUsers.value = []
  } finally {
    loadingUsers.value = false
  }
}

const closeUsersModal = () => {
  showUsersModal.value = false
  selectedRole.value = null
  roleUsers.value = []
}

const deleteRole = async (role) => {
  if (!confirm(`Tem certeza que deseja deletar a role ${role.name}?`)) return

  try {
    await adminApi.deleteRole(role.id)
    await loadRoles()
  } catch (err) {
    alert('Erro ao deletar role')
  }
}
</script>

<style scoped>
.super-roles {
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

.permissions-count {
  font-size: 0.875rem;
  color: #6b7280;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
}

.badge-blue {
  background-color: #dbeafe;
  color: #1e40af;
}

.badge-ativo {
  background-color: #d1fae5;
  color: #065f46;
}

.badge-inativo {
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

.role-form {
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
.form-group textarea {
  padding: 0.625rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  font-family: inherit;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.permissions-manager {
  max-height: 60vh;
}

.permissions-header {
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.permissions-header h3 {
  margin: 0 0 0.5rem;
  font-size: 1.25rem;
  color: #111827;
}

.permissions-header p {
  margin: 0;
  color: #6b7280;
  font-size: 0.875rem;
}

.permissions-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-height: 400px;
  overflow-y: auto;
}

.permission-item {
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 8px;
}

.checkbox-label {
  display: flex;
  align-items: start;
  gap: 0.75rem;
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  margin-top: 0.25rem;
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.checkbox-label strong {
  display: block;
  font-size: 0.875rem;
  color: #111827;
  margin-bottom: 0.25rem;
}

.checkbox-label p {
  margin: 0;
  font-size: 0.75rem;
  color: #6b7280;
}

.users-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.user-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 8px;
}

.user-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.user-info strong {
  font-size: 0.875rem;
  color: #111827;
}

.user-info span {
  font-size: 0.75rem;
  color: #6b7280;
}

.loading,
.no-data {
  text-align: center;
  padding: 2rem;
  color: #6b7280;
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
