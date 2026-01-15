<template>
  <div class="nova-solicitacao-container">
    <h1>Nova Solicitação</h1>

    <div v-if="successMessage" class="alert alert-success">
      {{ successMessage }}
    </div>

    <form @submit.prevent="submitForm" class="form-container">
      <!-- Título -->
      <div class="form-group">
        <label for="titulo">Título *</label>
        <input
          id="titulo"
          v-model="form.titulo"
          type="text"
          placeholder="Ex: Buraco na rua..."
          required
        />
        <small v-if="errors.titulo" class="error">{{ errors.titulo }}</small>
      </div>

      <!-- Descrição -->
      <div class="form-group">
        <label for="descricao">Descrição *</label>
        <textarea
          id="descricao"
          v-model="form.descricao"
          placeholder="Descreva o problema em detalhes..."
          rows="4"
          required
        ></textarea>
        <small v-if="errors.descricao" class="error">{{ errors.descricao }}</small>
      </div>

      <!-- Categoria -->
      <div class="form-group">
        <label for="categoria">Categoria *</label>
        <select id="categoria" v-model="form.categoria" required>
          <option value="">Selecione uma categoria</option>
          <option value="infraestrutura">Infraestrutura</option>
          <option value="iluminacao">Iluminação</option>
          <option value="limpeza">Limpeza</option>
          <option value="transito">Trânsito</option>
          <option value="saneamento">Saneamento</option>
          <option value="saude">Saúde</option>
          <option value="educacao">Educação</option>
          <option value="outro">Outro</option>
        </select>
        <small v-if="errors.categoria" class="error">{{ errors.categoria }}</small>
      </div>

      <!-- Endereço -->
      <div class="form-group">
        <label for="endereco">Endereço *</label>
        <input
          id="endereco"
          v-model="form.endereco"
          type="text"
          placeholder="Rua, número, bairro..."
          required
        />
        <small v-if="errors.endereco" class="error">{{ errors.endereco }}</small>
      </div>

      <!-- Bairro -->
      <div class="form-group">
        <label for="bairro">Bairro</label>
        <input
          id="bairro"
          v-model="form.bairro"
          type="text"
          placeholder="Bairro (preenchido automaticamente)"
        />
      </div>

      <!-- Geolocalização -->
      <div class="geolocation-section">
        <button
          type="button"
          :disabled="locatingGeo"
          @click="getGeolocation"
          class="btn btn-secondary"
        >
          📍 {{ locatingGeo ? 'Localizando...' : 'Usar GPS' }}
        </button>
        <small v-if="geoError" class="error">{{ geoError }}</small>
        <small v-if="form.latitude && form.longitude" class="success">
          ✓ Localização capturada
        </small>
      </div>

      <!-- Coordenadas -->
      <div class="coords-section">
        <div class="form-group">
          <label for="latitude">Latitude</label>
          <input
            id="latitude"
            v-model="form.latitude"
            type="number"
            step="0.000001"
            placeholder="Ex: -15.7942..."
            readonly
          />
        </div>
        <div class="form-group">
          <label for="longitude">Longitude</label>
          <input
            id="longitude"
            v-model="form.longitude"
            type="number"
            step="0.000001"
            placeholder="Ex: -48.0191..."
            readonly
          />
        </div>
      </div>

      <!-- Mapa -->
      <div v-if="showMap" class="map-container">
        <div id="map-element" class="map"></div>
        <small class="map-info">Arraste o marcador para ajustar a localização</small>
      </div>

      <!-- Foto/Anexo (futuro) -->
      <div class="form-group">
        <label for="foto">Foto (opcional)</label>
        <input
          id="foto"
          type="file"
          accept="image/*"
          @change="handleFileChange"
        />
        <small>Máximo 5MB, formatos: JPG, PNG</small>
      </div>

      <!-- Botões -->
      <div class="form-actions">
        <button type="submit" :disabled="submitting" class="btn btn-primary">
          {{ submitting ? 'Enviando...' : 'Enviar Solicitação' }}
        </button>
        <RouterLink to="/solicitacoes" class="btn btn-secondary">
          Cancelar
        </RouterLink>
      </div>

      <!-- Erros -->
      <div v-if="submitError" class="alert alert-error">
        {{ submitError }}
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { api } from '../lib/api'

let mapInstance = null
let marker = null

const router = useRouter()

const form = ref({
  titulo: '',
  descricao: '',
  categoria: '',
  endereco: '',
  bairro: '',
  latitude: '',
  longitude: '',
})

const errors = ref({})
const submitting = ref(false)
const submitError = ref('')
const successMessage = ref('')
const locatingGeo = ref(false)
const geoError = ref('')
const showMap = ref(false)

function validateForm() {
  errors.value = {}

  if (!form.value.titulo?.trim()) {
    errors.value.titulo = 'Título é obrigatório'
  }
  if (!form.value.descricao?.trim()) {
    errors.value.descricao = 'Descrição é obrigatória'
  }
  if (!form.value.categoria) {
    errors.value.categoria = 'Categoria é obrigatória'
  }
  if (!form.value.endereco?.trim()) {
    errors.value.endereco = 'Endereço é obrigatório'
  }

  return Object.keys(errors.value).length === 0
}

function getGeolocation() {
  locatingGeo.value = true
  geoError.value = ''

  if (!navigator.geolocation) {
    geoError.value = 'Geolocalização não suportada neste navegador'
    locatingGeo.value = false
    return
  }

  navigator.geolocation.getCurrentPosition(
    (position) => {
      form.value.latitude = position.coords.latitude.toFixed(6)
      form.value.longitude = position.coords.longitude.toFixed(6)
      locatingGeo.value = false
      showMap.value = true
      initMap()
    },
    (error) => {
      geoError.value = `Erro de localização: ${error.message}`
      locatingGeo.value = false
    }
  )
}

function initMap() {
  if (typeof window.L === 'undefined') {
    console.warn('Leaflet não carregado, mapa desativado')
    return
  }

  if (!mapInstance && form.value.latitude && form.value.longitude) {
    const mapElement = document.getElementById('map-element')
    if (!mapElement) return

    mapInstance = window.L.map('map-element').setView(
      [form.value.latitude, form.value.longitude],
      16
    )

    window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors',
      maxZoom: 19,
    }).addTo(mapInstance)

    marker = window.L.marker([form.value.latitude, form.value.longitude], {
      draggable: true,
    })
      .addTo(mapInstance)
      .on('dragend', () => {
        const lat = marker.getLatLng().lat
        const lng = marker.getLatLng().lng
        form.value.latitude = lat.toFixed(6)
        form.value.longitude = lng.toFixed(6)
      })
  } else if (mapInstance && form.value.latitude && form.value.longitude) {
    const newLat = parseFloat(form.value.latitude)
    const newLng = parseFloat(form.value.longitude)
    mapInstance.setView([newLat, newLng], 16)
    if (marker) {
      marker.setLatLng([newLat, newLng])
    }
  }
}

function handleFileChange(e) {
  // Para futuro: upload de arquivo
  const file = e.target.files?.[0]
  if (file && file.size > 5 * 1024 * 1024) {
    alert('Arquivo muito grande (máximo 5MB)')
    e.target.value = ''
  }
}

async function submitForm() {
  if (!validateForm()) return

  submitting.value = true
  submitError.value = ''
  successMessage.value = ''

  try {
    const payload = {
      titulo: form.value.titulo,
      descricao: form.value.descricao,
      categoria: form.value.categoria,
      endereco: form.value.endereco,
      bairro: form.value.bairro || '',
      latitude: form.value.latitude ? parseFloat(form.value.latitude) : null,
      longitude: form.value.longitude ? parseFloat(form.value.longitude) : null,
    }

    const { data } = await api.post('/problemas', payload)
    successMessage.value = 'Solicitação criada com sucesso!'
    
    setTimeout(() => {
      router.push(`/solicitacao/${data.id}`)
    }, 1500)
  } catch (e) {
    submitError.value = e?.response?.data?.message || 'Erro ao criar solicitação'
    console.error(e)
  } finally {
    submitting.value = false
  }
}

// Carregar Leaflet dinamicamente
onMounted(() => {
  if (!window.L) {
    const link = document.createElement('link')
    link.rel = 'stylesheet'
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css'
    document.head.appendChild(link)

    const script = document.createElement('script')
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js'
    script.onload = () => {
      // Leaflet carregado
    }
    document.head.appendChild(script)
  }
})
</script>

<style scoped>
.nova-solicitacao-container {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px 0;
}

h1 {
  margin-top: 0;
  margin-bottom: 24px;
  color: #e9eefc;
}

.alert {
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-size: 14px;
}

.alert-success {
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.3);
  color: #86efac;
}

.alert-error {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #fca5a5;
}

.form-container {
  background: #101e3d;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  padding: 24px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #e9eefc;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 10px 12px;
  background: #0f1a33;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  color: #e9eefc;
  font-size: 14px;
  font-family: inherit;
  box-sizing: border-box;
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: rgba(37, 99, 235, 0.5);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-group input[readonly] {
  background: #0a0f1f;
  cursor: not-allowed;
  opacity: 0.7;
}

.form-group small {
  display: block;
  margin-top: 6px;
  font-size: 12px;
  color: #a1afc7;
}

.error {
  color: #fca5a5;
}

.success {
  color: #86efac;
}

.geolocation-section {
  margin-bottom: 20px;
}

.coords-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-bottom: 20px;
}

.map-container {
  margin-bottom: 20px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.map {
  width: 100%;
  height: 300px;
  background: #0f1a33;
}

.map-info {
  display: block;
  padding: 8px 12px;
  background: rgba(255, 255, 255, 0.05);
  font-size: 12px;
  color: #a1afc7;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  font-weight: 500;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: #2563eb;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #1d4ed8;
  transform: translateY(-2px);
}

.btn-secondary {
  background: #64748b;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background: #475569;
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 24px;
}

.form-actions .btn {
  flex: 1;
}

@media (max-width: 768px) {
  .nova-solicitacao-container {
    padding: 12px;
  }

  .form-container {
    padding: 16px;
  }

  .coords-section {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column;
  }
}
</style>
