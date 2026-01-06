package com.scs.gestaocidada.ui.viewmodels

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.scs.gestaocidada.data.ApiClient
import com.scs.gestaocidada.data.models.Prefeitura
import com.scs.gestaocidada.data.models.Problema
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

class ProblemaViewModel : ViewModel() {

    private val _prefeituras = MutableStateFlow<List<Prefeitura>>(emptyList())
    val prefeituras: StateFlow<List<Prefeitura>> = _prefeituras.asStateFlow()

    private val _isLoadingPrefeituras = MutableStateFlow(false)
    val isLoadingPrefeituras: StateFlow<Boolean> = _isLoadingPrefeituras.asStateFlow()

    private val _meusProblemas = MutableStateFlow<List<Problema>>(emptyList())
    val meusProblemas: StateFlow<List<Problema>> = _meusProblemas.asStateFlow()

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> = _isLoading.asStateFlow()

    private val _errorMessage = MutableStateFlow<String?>(null)
    val errorMessage: StateFlow<String?> = _errorMessage.asStateFlow()

    fun loadPrefeituras() {
        viewModelScope.launch {
            _isLoadingPrefeituras.value = true
            try {
                val result = ApiClient.prefeituraService.getPrefeituras()
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
        prefeituraId: Int,
        latitude: Double?,
        longitude: Double?,
        onSuccess: (Int) -> Unit,
        onError: (String) -> Unit
    ) {
        viewModelScope.launch {
            try {
                val request = mapOf(
                    "titulo" to titulo,
                    "descricao" to descricao,
                    "bairro" to bairro,
                    "rua" to rua,
                    "numero" to numero,
                    "cep" to cep,
                    "prefeitura_id" to prefeituraId,
                    "latitude" to latitude,
                    "longitude" to longitude
                )
                
                val response = ApiClient.problemaService.createProblema(request)
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
                val result = ApiClient.problemaService.getMeusProblemas()
                _meusProblemas.value = result
            } catch (e: Exception) {
                _errorMessage.value = "Erro ao carregar problemas: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }
}
