package com.scs.gestaocidada.data

import com.scs.gestaocidada.data.models.*
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.PATCH
import retrofit2.http.Header
import retrofit2.http.Query
import retrofit2.http.Path

interface ApiService {
    @POST("/api/auth/login")
    suspend fun login(@Body req: LoginRequest): AuthResponse

    @POST("/api/auth/register")
    suspend fun register(@Body req: RegisterRequest): AuthResponse

    @POST("/api/auth/logout")
    suspend fun logout(@Header("Authorization") auth: String): Map<String, String>

    @GET("/api/prefeituras")
    suspend fun prefeituras(): List<PrefeituraDto>

    @POST("/api/problemas")
    suspend fun criarProblema(
        @Body req: ProblemaCreateRequest,
        @Header("Authorization") auth: String? = null
    ): ProblemaDto

    @GET("/api/problemas/mine")
    suspend fun meusProblemas(@Header("Authorization") auth: String): List<ProblemaDto>

    @GET("/api/geocode/reverse")
    suspend fun reverseGeocode(
        @Query("lat") lat: Double,
        @Query("lng") lng: Double
    ): ReverseGeocodeResponse

    @GET("/api/cep/{cep}")
    suspend fun consultarCep(@Path("cep") cep: String): ViaCepResponse

    // Admin endpoints
    @GET("/api/admin/problemas")
    suspend fun adminProblemas(
        @Query("status") status: String? = null,
        @Header("Authorization") auth: String
    ): List<ProblemaDto>

    @PATCH("/api/admin/problemas/{id}/status")
    suspend fun updateProblemaStatus(
        @Path("id") id: Long,
        @Body request: UpdateStatusRequest,
        @Header("Authorization") auth: String
    ): ProblemaDto
}

data class UpdateStatusRequest(
    val status: String
)
