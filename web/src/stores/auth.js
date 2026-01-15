import { defineStore } from 'pinia'
import { api, setToken } from '../lib/api'

const LS_KEY = 'gc_token'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem(LS_KEY) || '',
    me: null,
    loading: false,
    error: '',
  }),
  getters: {
    isAuthed: (s) => !!s.token,
    role: (s) => s.me?.role || s.me?.roles?.[0]?.name || '',
    isSuper: (s) => (s.me?.role === 'super' || s.me?.roles?.some?.(r=>r.name==='super')),
  },
  actions: {
    async bootstrap() {
      if (!this.token) return
      setToken(this.token)
      try {
        await this.fetchMe()
      } catch (e) {
        this.clear()
      }
    },
    clear() {
      this.token = ''
      this.me = null
      this.error = ''
      localStorage.removeItem(LS_KEY)
      setToken('')
    },
    async login(email, password) {
      this.loading = true; this.error=''
      try {
        const { data } = await api.post('/auth/login', { email, password })
        this.token = data?.token || data?.access_token || ''
        if (!this.token) throw new Error('Token não retornou')
        localStorage.setItem(LS_KEY, this.token)
        setToken(this.token)
        await this.fetchMe()
      } catch (e) {
        this.error = e?.response?.data?.message || e.message || 'Falha no login'
        throw e
      } finally {
        this.loading = false
      }
    },
    async register(payload) {
      this.loading = true; this.error=''
      try {
        const { data } = await api.post('/auth/register', payload)
        this.token = data?.token || data?.access_token || ''
        if (this.token) {
          localStorage.setItem(LS_KEY, this.token)
          setToken(this.token)
          await this.fetchMe()
        }
      } catch (e) {
        this.error = e?.response?.data?.message || e.message || 'Falha no cadastro'
        throw e
      } finally {
        this.loading = false
      }
    },
    async fetchMe() {
      // Some backends don't have /me; fallback by using /problemas/mine
      try {
        const { data } = await api.get('/me')
        this.me = data
        return
      } catch {
        // ignore
      }
      // If /me is missing, keep minimal
      this.me = { email: 'logado', role: 'cidadao' }
    },
    async logout() {
      if (!this.token) return
      try { await api.post('/auth/logout') } catch {}
      this.clear()
    }
  }
})
