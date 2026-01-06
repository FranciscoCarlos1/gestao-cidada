package com.scs.gestaocidada.ui.viewmodels

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.scs.gestaocidada.data.ApiClient
import com.scs.gestaocidada.data.TokenManager
import com.scs.gestaocidada.data.models.ProblemaDto
import com.scs.gestaocidada.data.models.UpdateStatusRequest
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

class AdminViewModel : ViewModel() {

    private val _problemas = MutableStateFlow<List<ProblemaDto>>(emptyList())
    val problemas: StateFlow<List<ProblemaDto>> = _problemas.asStateFlow()

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> = _isLoading.asStateFlow()

    private val _errorMessage = MutableStateFlow<String?>(null)
    val errorMessage: StateFlow<String?> = _errorMessage.asStateFlow()

    fun loadProblemas(status: String? = null) {
        viewModelScope.launch {
            _isLoading.value = true
            _errorMessage.value = null
            try {
                val token = TokenManager.getToken()
                if (token != null) {
                    val response = ApiClient.api.adminProblemas(
                        status = status,
                        auth = "Bearer $token"
                    )
                    _problemas.value = response
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

    fun updateStatus(problemaId: Long, newStatus: String) {
        viewModelScope.launch {
            try {
                val token = TokenManager.getToken()
                if (token != null) {
                    ApiClient.api.updateProblemaStatus(
                        id = problemaId,
                        request = UpdateStatusRequest(status = newStatus),
                        auth = "Bearer $token"
                    )
                    // Atualizar a lista localmente
                    _problemas.value = _problemas.value.map { problema ->
                        if (problema.id == problemaId) {
                            problema.copy(status = newStatus)
                        } else {
                            problema
                        }
                    }
                } else {
                    _errorMessage.value = "Token de autenticação não encontrado"
                }
            } catch (e: Exception) {
                _errorMessage.value = "Erro ao atualizar status: ${e.message}"
            }
        }
    }
}
