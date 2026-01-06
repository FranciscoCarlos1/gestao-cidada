package com.scs.gestaocidada.ui.viewmodels

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.scs.gestaocidada.data.ApiClient
import com.scs.gestaocidada.data.TokenManager
import com.scs.gestaocidada.data.models.LoginRequest
import com.scs.gestaocidada.data.models.RegisterRequest
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
                    LoginRequest(
                        email = email,
                        password = password
                    )
                )
                
                TokenManager.saveToken(response.token)
                TokenManager.saveUserRole(response.user.role)
                TokenManager.saveUserInfo(response.user.id, response.user.name, response.user.email)
                
                onSuccess(response.user.role)
            } catch (e: Exception) {
                onError(e.message ?: "Erro ao fazer login")
            }
        }
    }

    fun register(
        name: String,
        email: String,
        password: String,
        onSuccess: (String) -> Unit,
        onError: (String) -> Unit
    ) {
        viewModelScope.launch {
            try {
                val response = ApiClient.authService.register(
                    RegisterRequest(
                        name = name,
                        email = email,
                        password = password
                    )
                )
                
                TokenManager.saveToken(response.token)
                TokenManager.saveUserRole(response.user.role)
                TokenManager.saveUserInfo(response.user.id, response.user.name, response.user.email)
                
                onSuccess(response.user.role)
            } catch (e: Exception) {
                onError(e.message ?: "Erro ao registrar")
            }
        }
    }

    fun logout(onComplete: () -> Unit) {
        viewModelScope.launch {
            try {
                val token = TokenManager.getToken()
                if (token != null) {
                    ApiClient.authService.logout("Bearer $token")
                }
            } catch (e: Exception) {
                // Ignorar erros no logout
            } finally {
                TokenManager.clearAll()
                onComplete()
            }
        }
    }
}
