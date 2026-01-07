package com.scs.gestaocidada.ui.viewmodels

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.scs.gestaocidada.data.ApiClient
import com.scs.gestaocidada.data.TokenManager
import com.scs.gestaocidada.data.models.PrefeituraDto
import com.scs.gestaocidada.data.models.ProblemaCreateRequest
import com.scs.gestaocidada.data.models.ProblemaDto
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

class ProblemaViewModel : ViewModel() {

    private val _prefeituras = MutableStateFlow<List<PrefeituraDto>>(emptyList())
    val prefeituras: StateFlow<List<PrefeituraDto>> = _prefeituras.asStateFlow()

    private val _isLoadingPrefeituras = MutableStateFlow(false)
    val isLoadingPrefeituras: StateFlow<Boolean> = _isLoadingPrefeituras.asStateFlow()

    private val _meusProblemas = MutableStateFlow<List<ProblemaDto>>(emptyList())
    val meusProblemas: StateFlow<List<ProblemaDto>> = _meusProblemas.asStateFlow()

    private val _problemasPublicos = MutableStateFlow<List<ProblemaDto>>(emptyList())
    val problemasPublicos: StateFlow<List<ProblemaDto>> = _problemasPublicos.asStateFlow()

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> = _isLoading.asStateFlow()

    private val _errorMessage = MutableStateFlow<String?>(null)
    val errorMessage: StateFlow<String?> = _errorMessage.asStateFlow()

    fun loadPrefeituras() {
        viewModelScope.launch {
            _isLoadingPrefeituras.value = true
            try {
                val result = ApiClient.api.prefeituras()
                _prefeituras.value = result
            } catch (e: Exception) {
                _errorMessage.value = "Erro ao carregar prefeituras: ${e.message}"
            } finally {
                _isLoadingPrefeituras.value = false
            }
        }
    }

    fun submitProblema(
        titulo: String,
        descricao: String,
        bairro: String,
        rua: String?,
        numero: String?,
        cep: String?,
        prefeituraId: Long,
        latitude: Double?,
        longitude: Double?,
        onSuccess: (Long) -> Unit,
        onError: (String) -> Unit
    ) {
        viewModelScope.launch {
            try {
                val request = ProblemaCreateRequest(
                    titulo = titulo,
                    descricao = descricao,
                    bairro = bairro,
                    rua = rua,
                    numero = numero,
                    cep = cep,
                    prefeituraId = prefeituraId,
                    latitude = latitude,
                    longitude = longitude
                )
                
                val response = ApiClient.api.criarProblema(request)
                _meusProblemas.value += response
                onSuccess(response.id)
            } catch (e: Exception) {
                onError(e.message ?: "Erro ao enviar problema")
            }
        }
    }

    fun loadMeusProblemas() {
        viewModelScope.launch {
            _isLoading.value = true
            _errorMessage.value = null
            try {
                val token = TokenManager.getToken()
                if (token != null) {
                    val result = ApiClient.api.meusProblemas("Bearer $token")
                    _meusProblemas.value = result
                } else {
                    _errorMessage.value = "Token de autenticação não encontrado"
                }
            } catch (e: Exception) {
                _errorMessage.value = "Erro ao carregar problemas: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun loadProblemasPublicos(status: String? = null, prefeituraId: Long? = null) {
        viewModelScope.launch {
            _isLoading.value = true
            _errorMessage.value = null
            try {
                val result = ApiClient.api.problemas(
                    status = status,
                    prefeituraId = prefeituraId
                )
                _problemasPublicos.value = result
            } catch (e: Exception) {
                _errorMessage.value = "Erro ao carregar problemas públicos: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }
}
