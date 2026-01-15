import { defineStore } from 'pinia'
import * as adminApi from '../lib/api'

export const useAdminStore = defineStore('admin', {
  state: () => ({
    users: [],
    roles: [],
    permissions: [],
    prefeituras: [],
    stats: {
      totalUsers: 0,
      totalPrefeituras: 0,
      totalProblemas: 0,
      problemasPendentes: 0,
      problemasAndamento: 0,
      problemasResolvidos: 0
    },
    loading: false,
    error: null
  }),

  actions: {
    async loadDashboardStats() {
      this.loading = true
      this.error = null
      try {
        this.stats = await adminApi.getDashboardStats()
      } catch (error) {
        this.error = error.message
        console.error('Erro ao carregar estatísticas:', error)
      } finally {
        this.loading = false
      }
    },

    async loadUsers(filters = {}) {
      this.loading = true
      this.error = null
      try {
        this.users = await adminApi.listUsers(filters)
      } catch (error) {
        this.error = error.message
        console.error('Erro ao carregar usuários:', error)
      } finally {
        this.loading = false
      }
    },

    async loadRoles() {
      this.loading = true
      this.error = null
      try {
        this.roles = await adminApi.listRoles()
      } catch (error) {
        this.error = error.message
        console.error('Erro ao carregar roles:', error)
      } finally {
        this.loading = false
      }
    },

    async loadPermissions() {
      this.loading = true
      this.error = null
      try {
        this.permissions = await adminApi.listPermissions()
      } catch (error) {
        this.error = error.message
        console.error('Erro ao carregar permissões:', error)
      } finally {
        this.loading = false
      }
    },

    async loadPrefeituras(filters = {}) {
      this.loading = true
      this.error = null
      try {
        this.prefeituras = await adminApi.listPrefeituras(filters)
      } catch (error) {
        this.error = error.message
        console.error('Erro ao carregar prefeituras:', error)
      } finally {
        this.loading = false
      }
    },

    clearError() {
      this.error = null
    }
  }
})
