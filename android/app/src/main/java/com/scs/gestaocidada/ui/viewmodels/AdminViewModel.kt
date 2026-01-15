package com.scs.gestaocidada.ui.viewmodels

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.scs.gestaocidada.data.ApiClient
import com.scs.gestaocidada.data.TokenManager
import com.scs.gestaocidada.data.models.AuditLogDto
import com.scs.gestaocidada.data.models.ProblemaDto
import com.scs.gestaocidada.data.models.RoleDto
import com.scs.gestaocidada.data.models.ToggleStatusRequest
import com.scs.gestaocidada.data.models.UpdateStatusRequest
import com.scs.gestaocidada.data.models.UserDto
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

class AdminViewModel : ViewModel() {

    // Admin (Prefeitura)
    private val _problemas = MutableStateFlow<List<ProblemaDto>>(emptyList())
    val problemas: StateFlow<List<ProblemaDto>> = _problemas.asStateFlow()

    // Super Admin
    private val _users = MutableStateFlow<List<UserDto>>(emptyList())
    val users: StateFlow<List<UserDto>> = _users.asStateFlow()

    private val _roles = MutableStateFlow<List<RoleDto>>(emptyList())
    val roles: StateFlow<List<RoleDto>> = _roles.asStateFlow()

    private val _auditLogs = MutableStateFlow<List<AuditLogDto>>(emptyList())
    val auditLogs: StateFlow<List<AuditLogDto>> = _auditLogs.asStateFlow()

    // UI State
    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> = _isLoading.asStateFlow()

    private val _errorMessage = MutableStateFlow<String?>(null)
    val errorMessage: StateFlow<String?> = _errorMessage.asStateFlow()

    private val _successMessage = MutableStateFlow<String?>(null)
    val successMessage: StateFlow<String?> = _successMessage.asStateFlow()

    // -------- Admin Prefeitura --------
    fun loadProblemas(status: String? = null, page: Int = 1, perPage: Int = 15) {
        _isLoading.value = true
        _errorMessage.value = null
        val token = TokenManager.getToken()
        if (token == null) {
            _errorMessage.value = "Token de autenticação não encontrado"
            _isLoading.value = false
            return
        }
        viewModelScope.launch {
            try {
                val response = ApiClient.api.adminProblemas(
                    status = status,
                    page = page,
                    perPage = perPage,
                    auth = "Bearer $token"
                )
                _problemas.value = response.data
            } catch (e: Exception) {
                _errorMessage.value = "Erro ao carregar problemas: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun updateStatus(problemaId: Long, newStatus: String) {
        _errorMessage.value = null
        val token = TokenManager.getToken()
        if (token == null) {
            _errorMessage.value = "Token de autenticação não encontrado"
            return
        }
        viewModelScope.launch {
            try {
                val updated = ApiClient.api.updateProblemaStatus(
                    id = problemaId,
                    request = UpdateStatusRequest(status = newStatus),
                    auth = "Bearer $token"
                )
                _problemas.value = _problemas.value.map { if (it.id == problemaId) updated else it }
                _successMessage.value = "Status do problema atualizado"
            } catch (e: Exception) {
                _errorMessage.value = "Erro ao atualizar status: ${e.message}"
            }
        }
    }

    // -------- Super Admin: Users --------
    fun loadUsers(
        search: String? = null,
        role: String? = null,
        page: Int = 1,
        perPage: Int = 15
    ) {
        _isLoading.value = true
        _errorMessage.value = null
        val token = TokenManager.getToken()
        if (token == null) {
            _errorMessage.value = "Token de autenticação não encontrado"
            _isLoading.value = false
            return
        }
        viewModelScope.launch {
            try {
                val response = ApiClient.api.adminUsers(
                    search = search,
                    role = role,
                    page = page,
                    perPage = perPage,
                    auth = "Bearer $token"
                )
                _users.value = response.data
            } catch (e: Exception) {
                _errorMessage.value = "Erro ao carregar usuários: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun deleteUser(userId: Long) {
        _isLoading.value = true
        _errorMessage.value = null
        val token = TokenManager.getToken()
        if (token == null) {
            _errorMessage.value = "Token de autenticação não encontrado"
            _isLoading.value = false
            return
        }
        viewModelScope.launch {
            try {
                ApiClient.api.deleteUser(userId, auth = "Bearer $token")
                _successMessage.value = "Usuário removido com sucesso"
                loadUsers()
            } catch (e: Exception) {
                _errorMessage.value = "Erro ao remover usuário: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun toggleUserStatus(userId: Long) {
        _isLoading.value = true
        _errorMessage.value = null
        val token = TokenManager.getToken()
        if (token == null) {
            _errorMessage.value = "Token de autenticação não encontrado"
            _isLoading.value = false
            return
        }
        viewModelScope.launch {
            try {
                // Encontra o usuário e alterna o status
                val currentUser = _users.value.find { it.id == userId }
                val newStatus = if (currentUser?.status == "suspended") "active" else "suspended"
                
                ApiClient.api.toggleUserStatus(
                    id = userId,
                    req = ToggleStatusRequest(status = newStatus),
                    auth = "Bearer $token"
                )
                _successMessage.value = "Status do usuário atualizado"
                loadUsers()
            } catch (e: Exception) {
                _errorMessage.value = "Erro ao atualizar status: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun resetUserPassword(userId: Long) {
        _isLoading.value = true
        _errorMessage.value = null
        val token = TokenManager.getToken()
        if (token == null) {
            _errorMessage.value = "Token de autenticação não encontrado"
            _isLoading.value = false
            return
        }
        viewModelScope.launch {
            try {
                ApiClient.api.resetPassword(
                    id = userId,
                    auth = "Bearer $token"
                )
                _successMessage.value = "Senha resetada com sucesso"
            } catch (e: Exception) {
                _errorMessage.value = "Erro ao resetar senha: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    // -------- Super Admin: Roles --------
    fun loadRoles() {
        _isLoading.value = true
        _errorMessage.value = null
        val token = TokenManager.getToken()
        if (token == null) {
            _errorMessage.value = "Token de autenticação não encontrado"
            _isLoading.value = false
            return
        }
        viewModelScope.launch {
            try {
                val response = ApiClient.api.getRoles(auth = "Bearer $token")
                _roles.value = response.data
            } catch (e: Exception) {
                _errorMessage.value = "Erro ao carregar roles: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    // -------- Super Admin: Audit Logs --------
    fun loadAuditLogs(
        userId: Long? = null,
        action: String? = null,
        page: Int = 1,
        perPage: Int = 50
    ) {
        _isLoading.value = true
        _errorMessage.value = null
        val token = TokenManager.getToken()
        if (token == null) {
            _errorMessage.value = "Token de autenticação não encontrado"
            _isLoading.value = false
            return
        }
        viewModelScope.launch {
            try {
                val response = ApiClient.api.auditLogs(
                    userId = userId,
                    action = action,
                    page = page,
                    perPage = perPage,
                    auth = "Bearer $token"
                )
                _auditLogs.value = response.data
            } catch (e: Exception) {
                _errorMessage.value = "Erro ao carregar logs: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun clearMessages() {
        _errorMessage.value = null
        _successMessage.value = null
    }
}
