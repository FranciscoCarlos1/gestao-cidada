package com.scs.gestaocidada.ui.viewmodels

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.scs.gestaocidada.data.ApiClient
import com.scs.gestaocidada.data.TokenManager
import kotlinx.coroutines.launch

class AuthViewModel : ViewModel() {

    fun login(
        email: String,
        password: String,
        onSuccess: (String) -> Unit,
        onError: (String) -> Unit
    ) {
        viewModelScope.launch {
            try {
                val response = ApiClient.authService.login(
                    mapOf(
                        "email" to email,
                        "password" to password
                    )
                )
                
                TokenManager.saveToken(response.token)
                TokenManager.saveUserRole(response.user.role)
                
                onSuccess(response.user.role)
            } catch (e: Exception) {
                onError(e.message ?: "Erro ao fazer login")
            }
        }
    }

    fun logout(onComplete: () -> Unit) {
        viewModelScope.launch {
            try {
                ApiClient.authService.logout()
            } catch (e: Exception) {
                // Ignorar erros no logout
            } finally {
                TokenManager.clearToken()
                TokenManager.clearUserRole()
                onComplete()
            }
        }
    }
}
