package com.scs.gestaocidada.ui.viewmodels

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.scs.gestaocidada.data.ApiClient
import com.scs.gestaocidada.data.models.Problema
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

class AdminViewModel : ViewModel() {

    private val _problemas = MutableStateFlow<List<Problema>>(emptyList())
    val problemas: StateFlow<List<Problema>> = _problemas.asStateFlow()

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> = _isLoading.asStateFlow()

    private val _errorMessage = MutableStateFlow<String?>(null)
    val errorMessage: StateFlow<String?> = _errorMessage.asStateFlow()

    fun loadProblemas(status: String? = null) {
        viewModelScope.launch {
            _isLoading.value = true
            _errorMessage.value = null
            try {
                val response = ApiClient.adminService.getProblemas(status)
                _problemas.value = response.data
            } catch (e: Exception) {
                _errorMessage.value = "Erro ao carregar problemas: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun updateStatus(problemaId: Int, newStatus: String) {
        viewModelScope.launch {
            try {
                ApiClient.adminService.updateStatus(
                    problemaId,
                    mapOf("status" to newStatus)
                )
                // Atualizar a lista localmente
                _problemas.value = _problemas.value.map { problema ->
                    if (problema.id == problemaId) {
                        problema.copy(status = newStatus)
                    } else {
                        problema
                    }
                }
            } catch (e: Exception) {
                _errorMessage.value = "Erro ao atualizar status: ${e.message}"
            }
        }
    }
}
