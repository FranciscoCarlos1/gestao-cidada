import axios from 'axios'

const baseURL = import.meta.env.VITE_API_BASE || 'http://localhost:8080/api'

export const api = axios.create({
  baseURL,
  timeout: 20000,
})

export function setToken(token) {
  if (token) api.defaults.headers.common['Authorization'] = `Bearer ${token}`
  else delete api.defaults.headers.common['Authorization']
}

// ========== SOLICITAÇÕES (Problemas) ==========

export async function listSolicitacoes() {
  const { data } = await api.get('/problemas/mine')
  return data || data.data || []
}

export async function getSolicitacao(id) {
  const { data } = await api.get(`/problemas/${id}`)
  return data
}

export async function createSolicitacao(payload) {
  const { data } = await api.post('/problemas', payload)
  return data
}

export async function updateSolicitacao(id, payload) {
  const { data } = await api.put(`/problemas/${id}`, payload)
  return data
}

export async function deleteSolicitacao(id) {
  await api.delete(`/problemas/${id}`)
}

// ========== GEOLOCALIZAÇÃO E ENDEREÇO ==========

export async function buscarEndereçoPorCEP(cep) {
  try {
    // Tenta via backend primeiro
    const { data } = await api.get(`/geocode/cep/${cep}`)
    return data
  } catch {
    // Fallback: usa ViaCEP diretamente
    const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`)
    return response.json()
  }
}

export async function buscarEnderecoPorGeolocation(latitude, longitude) {
  try {
    const { data } = await api.get(`/geocode/reverse`, {
      params: { lat: latitude, lng: longitude }
    })
    return data
  } catch (e) {
    console.error('Erro ao buscar endereço', e)
    return null
  }
}

// ========== SUPER ADMIN - USUÁRIOS ==========

export async function listUsers(filters = {}) {
  const { data } = await api.get('/admin/users', { params: filters })
  return data.data || data
}

export async function getUser(id) {
  const { data } = await api.get(`/admin/users/${id}`)
  return data
}

export async function createUser(payload) {
  const { data } = await api.post('/admin/users', payload)
  return data
}

export async function updateUser(id, payload) {
  const { data } = await api.put(`/admin/users/${id}`, payload)
  return data
}

export async function deleteUser(id) {
  await api.delete(`/admin/users/${id}`)
}

export async function updateUserStatus(id, status) {
  const { data } = await api.patch(`/admin/users/${id}/status`, { status })
  return data
}

// ========== SUPER ADMIN - ROLES & PERMISSÕES ==========

export async function listRoles() {
  const { data } = await api.get('/admin/roles')
  return data.data || data
}

export async function getRole(id) {
  const { data } = await api.get(`/admin/roles/${id}`)
  return data
}

export async function createRole(payload) {
  const { data } = await api.post('/admin/roles', payload)
  return data
}

export async function updateRole(id, payload) {
  const { data } = await api.put(`/admin/roles/${id}`, payload)
  return data
}

export async function deleteRole(id) {
  await api.delete(`/admin/roles/${id}`)
}

export async function listPermissions() {
  const { data } = await api.get('/admin/permissions')
  return data.data || data
}

export async function assignPermissions(roleId, permissionIds) {
  const { data } = await api.post(`/admin/roles/${roleId}/permissions`, { permissions: permissionIds })
  return data
}

export async function getRoleUsers(roleId) {
  const { data } = await api.get(`/admin/roles/${roleId}/users`)
  return data.data || data
}

// ========== SUPER ADMIN - PREFEITURAS ==========

export async function listPrefeituras(filters = {}) {
  const { data } = await api.get('/admin/prefeituras', { params: filters })
  return data.data || data
}

export async function getPrefeitura(id) {
  const { data } = await api.get(`/admin/prefeituras/${id}`)
  return data
}

export async function createPrefeitura(payload) {
  const { data } = await api.post('/admin/prefeituras', payload)
  return data
}

export async function updatePrefeitura(id, payload) {
  const { data } = await api.put(`/admin/prefeituras/${id}`, payload)
  return data
}

export async function deletePrefeitura(id) {
  await api.delete(`/admin/prefeituras/${id}`)
}

export async function getPrefeituraStats(id) {
  const { data } = await api.get(`/admin/prefeituras/${id}/stats`)
  return data
}

// ========== DASHBOARD & ESTATÍSTICAS ==========

export async function getDashboardStats() {
  const { data } = await api.get('/admin/dashboard/stats')
  return data
}

export async function getRecentActivities(limit = 10) {
  const { data } = await api.get('/admin/activities', { params: { limit } })
  return data.data || data
}

// ========== RELATÓRIOS ==========

export async function getProblemasReport(filters = {}) {
  const { data } = await api.get('/admin/reports/problemas', { params: filters })
  return data
}

export async function exportProblemasCSV(filters = {}) {
  const { data } = await api.get('/admin/reports/problemas/export', { 
    params: filters,
    responseType: 'text'
  })
  return data
}

export async function getUsersReport(filters = {}) {
  const { data } = await api.get('/admin/reports/users', { params: filters })
  return data
}

export async function exportUsuariosCSV(filters = {}) {
  const { data } = await api.get('/admin/reports/users/export', { 
    params: filters,
    responseType: 'text'
  })
  return data
}

export async function getAuditLogs(filters = {}) {
  const { data } = await api.get('/admin/audit-logs', { params: filters })
  return data.data || data
}

export async function exportAuditLogsCSV(filters = {}) {
  const { data } = await api.get('/admin/audit-logs/export', { 
    params: filters,
    responseType: 'text'
  })
  return data
}
