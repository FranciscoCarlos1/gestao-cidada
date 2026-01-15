package com.scs.gestaocidada.ui.viewmodels

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.scs.gestaocidada.data.ApiClient
import com.scs.gestaocidada.data.TokenManager
import com.scs.gestaocidada.data.models.TwoFAConfirmRequest
import com.scs.gestaocidada.data.models.TwoFADisableRequest
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

class TwoFactorViewModel : ViewModel() {
    private val _qrCodeUrl = MutableStateFlow<String?>(null)
    val qrCodeUrl: StateFlow<String?> = _qrCodeUrl.asStateFlow()

    private val _backupCodes = MutableStateFlow<List<String>>(emptyList())
    val backupCodes: StateFlow<List<String>> = _backupCodes.asStateFlow()

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> = _isLoading.asStateFlow()

    private val _error = MutableStateFlow<String?>(null)
    val error: StateFlow<String?> = _error.asStateFlow()

    private val _success = MutableStateFlow<String?>(null)
    val success: StateFlow<String?> = _success.asStateFlow()

    fun generate2FA(token: String) {
        _isLoading.value = true
        _error.value = null

        viewModelScope.launch {
            try {
                val response = ApiClient.api.generate2FA("Bearer $token")
                _qrCodeUrl.value = response.qr_code_url
                _success.value = response.message ?: "QR Code gerado com sucesso"
            } catch (e: Exception) {
                _error.value = "Erro ao gerar QR Code: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun confirm2FA(token: String, totpCode: String) {
        _isLoading.value = true
        _error.value = null

        viewModelScope.launch {
            try {
                val response = ApiClient.api.confirm2FA(
                    TwoFAConfirmRequest(totpCode),
                    "Bearer $token"
                )
                _backupCodes.value = response.backup_codes
                _success.value = response.message
            } catch (e: Exception) {
                _error.value = "Erro ao confirmar 2FA: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun disable2FA(token: String, password: String) {
        _isLoading.value = true
        _error.value = null

        viewModelScope.launch {
            try {
                ApiClient.api.disable2FA(
                    TwoFADisableRequest(password),
                    "Bearer $token"
                )
                _success.value = "2FA desativado com sucesso"
                _qrCodeUrl.value = null
                _backupCodes.value = emptyList()
            } catch (e: Exception) {
                _error.value = "Erro ao desativar 2FA: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun clearError() {
        _error.value = null
    }

    fun clearSuccess() {
        _success.value = null
    }
}
