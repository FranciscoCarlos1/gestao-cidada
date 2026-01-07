package com.scs.gestaocidada.data

import com.scs.gestaocidada.data.models.*
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.PATCH
import retrofit2.http.DELETE
import retrofit2.http.Header
import retrofit2.http.Query
import retrofit2.http.Path

interface ApiService {
    // ==================== AUTH ====================
    @POST("/api/auth/register")
    suspend fun register(@Body req: RegisterRequest): AuthResponse

    @POST("/api/auth/login")
    suspend fun login(@Body req: LoginRequest): AuthResponse

    @POST("/api/auth/anonimo")
    suspend fun loginAnonymous(): AnonResponse

    @POST("/api/auth/logout")
    suspend fun logout(@Header("Authorization") auth: String): Map<String, String>

    // ==================== 2FA ====================
    @POST("/api/2fa/generate")
    suspend fun generate2FA(@Header("Authorization") auth: String): TwoFAGenerateResponse

    @POST("/api/2fa/confirm")
    suspend fun confirm2FA(
        @Body req: TwoFAConfirmRequest,
        @Header("Authorization") auth: String
    ): TwoFAConfirmResponse

    @POST("/api/2fa/disable")
    suspend fun disable2FA(
        @Body req: TwoFADisableRequest,
        @Header("Authorization") auth: String
    ): Map<String, String>

    // ==================== PUBLIC DATA ====================
    @GET("/api/prefeituras")
    suspend fun prefeituras(@Query("search") search: String? = null): PrefeituraListResponse

    @GET("/api/problemas")
    suspend fun problemas(
        @Query("prefeitura_id") prefeituraId: Int? = null,
        @Query("status") status: String? = null,
        @Query("search") search: String? = null,
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 15
    ): ProblemaListResponse

    @GET("/api/geocode/reverse")
    suspend fun reverseGeocode(
        @Query("lat") lat: Double,
        @Query("lng") lng: Double
    ): ReverseGeocodeResponse

    @GET("/api/cep/{cep}")
    suspend fun consultarCep(@Path("cep") cep: String): ViaCepResponse

    // ==================== CITIZEN ====================
    @POST("/api/problemas")
    suspend fun criarProblema(
        @Body req: ProblemaCreateRequest,
        @Header("Authorization") auth: String? = null
    ): ProblemaDto

    @GET("/api/problemas/mine")
    suspend fun meusProblemas(
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 15,
        @Header("Authorization") auth: String
    ): ProblemaListResponse

    @GET("/api/problemas/{id}")
    suspend fun problemaDetail(
        @Path("id") id: Long,
        @Header("Authorization") auth: String? = null
    ): ProblemaDto

    // ==================== ADMIN PREFEITURA ====================
    @GET("/api/admin/problemas")
    suspend fun adminProblemas(
        @Query("status") status: String? = null,
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 15,
        @Header("Authorization") auth: String
    ): ProblemaListResponse

    @PATCH("/api/admin/problemas/{id}/status")
    suspend fun updateProblemaStatus(
        @Path("id") id: Long,
        @Body request: UpdateStatusRequest,
        @Header("Authorization") auth: String
    ): ProblemaDto

    // ==================== SUPER ADMIN ====================
    @GET("/api/admin/users")
    suspend fun adminUsers(
        @Query("search") search: String? = null,
        @Query("role") role: String? = null,
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 15,
        @Header("Authorization") auth: String
    ): UserListResponse

    @POST("/api/admin/users")
    suspend fun createUser(
        @Body req: CreateUserRequest,
        @Header("Authorization") auth: String
    ): UserDto

    @GET("/api/admin/users/{id}")
    suspend fun getUser(
        @Path("id") id: Long,
        @Header("Authorization") auth: String
    ): UserDto

    @PATCH("/api/admin/users/{id}")
    suspend fun updateUser(
        @Path("id") id: Long,
        @Body req: UpdateUserRequest,
        @Header("Authorization") auth: String
    ): UserDto

    @DELETE("/api/admin/users/{id}")
    suspend fun deleteUser(
        @Path("id") id: Long,
        @Header("Authorization") auth: String
    ): Map<String, String>

    @PATCH("/api/admin/users/{id}/toggle-status")
    suspend fun toggleUserStatus(
        @Path("id") id: Long,
        @Body req: ToggleStatusRequest,
        @Header("Authorization") auth: String
    ): UserDto

    @GET("/api/admin/roles")
    suspend fun getRoles(@Header("Authorization") auth: String): RoleListResponse

    @GET("/api/admin/audit-logs")
    suspend fun auditLogs(
        @Query("user_id") userId: Long? = null,
        @Query("action") action: String? = null,
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 50,
        @Header("Authorization") auth: String
    ): AuditLogListResponse
}
