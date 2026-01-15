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
