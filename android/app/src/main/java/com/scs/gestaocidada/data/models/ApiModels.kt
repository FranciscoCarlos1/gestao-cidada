package com.scs.gestaocidada.data.models

import com.squareup.moshi.Json

// ==================== AUTH ====================
data class LoginRequest(
    val email: String,
    val password: String,
    val totp_code: String? = null
)

data class RegisterRequest(
    val name: String,
    val email: String,
    val password: String
)

data class AuthResponse(
    val user: UserDto,
    val token: String,
    val requires_2fa: Boolean? = false,
    val message: String? = null
)

data class AnonResponse(
    val token: String,
    val type: String,
    val user: UserDto? = null
)

data class UserDto(
    val id: Long,
    val name: String,
    val email: String,
    val role: String,
    @Json(name = "role_id")
    val roleId: Long? = null,
    @Json(name = "prefeitura_id")
    val prefeituraId: Long? = null,
    @Json(name = "two_factor_enabled")
    val twoFactorEnabled: Boolean? = false,
    @Json(name = "last_login_at")
    val lastLoginAt: String? = null,
    val status: String? = "active",
    val metadata: Map<String, Any>? = null
)

data class UserListResponse(
    val data: List<UserDto>,
    val current_page: Int? = 1,
    val total: Int? = 0
)

// ==================== 2FA ====================
data class TwoFAGenerateResponse(
    val secret: String,
    val qr_code_url: String,
    val message: String? = null
)

data class TwoFAConfirmRequest(
    val totp_code: String
)

data class TwoFAConfirmResponse(
    val message: String,
    val backup_codes: List<String>
)

data class TwoFADisableRequest(
    val password: String
)

// ==================== PREFEITURA ====================
data class PrefeituraDto(
    val id: Long,
    val nome: String,
    val slug: String? = null,
    val cnpj: String? = null,
    val email_contato: String? = null,
    val telefone: String? = null,
    val cidade: String? = null,
    val uf: String? = null
)

data class PrefeituraListResponse(
    val data: List<PrefeituraDto>,
    val current_page: Int? = 1,
    val total: Int? = 0
)

// ==================== PROBLEMA ====================
data class ProblemaDto(
    val id: Long,
    val titulo: String,
    val descricao: String,
    val bairro: String? = null,
    val rua: String? = null,
    val numero: String? = null,
    val cep: String? = null,
    val cidade: String? = null,
    val uf: String? = null,
    val latitude: Double? = null,
    val longitude: Double? = null,
    val status: String = "aberto",
    @Json(name = "status_history")
    val statusHistory: List<StatusChange>? = null,
    @Json(name = "user_id")
    val userId: Long? = null,
    val user: UserDto? = null,
    @Json(name = "prefeitura_id")
    val prefeituraId: Long? = null,
    val prefeitura: PrefeituraDto? = null,
    @Json(name = "assigned_to")
    val assignedTo: Long? = null,
    @Json(name = "internal_notes")
    val internalNotes: String? = null,
    @Json(name = "resolved_at")
    val resolvedAt: String? = null,
    @Json(name = "created_at")
    val createdAt: String? = null,
    @Json(name = "updated_at")
    val updatedAt: String? = null
)

data class StatusChange(
    val from: String,
    val to: String,
    val reason: String? = null,
    val changed_at: String,
    val changed_by: Long? = null
)

data class ProblemaCreateRequest(
    val titulo: String,
    val descricao: String,
    val bairro: String,
    val rua: String? = null,
    val numero: String? = null,
    val complemento: String? = null,
    val cep: String? = null,
    val cidade: String? = null,
    val uf: String? = null,
    val latitude: Double? = null,
    val longitude: Double? = null,
    val prefeitura_id: Long
)

data class ProblemaListResponse(
    val data: List<ProblemaDto>,
    val current_page: Int? = 1,
    val total: Int? = 0
)

data class UpdateStatusRequest(
    val status: String,
    val internal_notes: String? = null,
    val assigned_to: Long? = null
)

// ==================== GEOCODING ====================
data class ReverseGeocodeResponse(
    val address: Map<String, String>? = null,
    val latitude: Double? = null,
    val longitude: Double? = null
)

data class ViaCepResponse(
    val cep: String? = null,
    val logradouro: String? = null,
    val complemento: String? = null,
    val bairro: String? = null,
    val localidade: String? = null,
    val uf: String? = null,
    val erro: Boolean? = false
)

// ==================== ROLES & PERMISSIONS ====================
data class RoleDto(
    val id: Long,
    val name: String,
    val description: String? = null,
    val permissions: List<PermissionDto>? = null
)

data class RoleListResponse(
    val data: List<RoleDto>
)

data class PermissionDto(
    val id: Long,
    val name: String,
    val description: String? = null
)

// ==================== AUDIT ====================
data class AuditLogDto(
    val id: Long,
    val user_id: Long? = null,
    val user: UserDto? = null,
    val action: String,
    val model_type: String? = null,
    val model_id: Long? = null,
    val changes: Map<String, Any>? = null,
    val ip_address: String? = null,
    val user_agent: String? = null,
    val created_at: String
)

data class AuditLogListResponse(
    val data: List<AuditLogDto>,
    val current_page: Int? = 1,
    val total: Int? = 0
)

// ==================== ADMIN USER MANAGEMENT ====================
data class CreateUserRequest(
    val name: String,
    val email: String,
    val password: String,
    val role: String,
    val role_id: Long? = null,
    val prefeitura_id: Long? = null,
    val status: String = "active"
)

data class UpdateUserRequest(
    val name: String? = null,
    val email: String? = null,
    val role: String? = null,
    val role_id: Long? = null,
    val prefeitura_id: Long? = null,
    val status: String? = null
)

data class ToggleStatusRequest(
    val status: String
)
 
