package com.scs.gestaocidada.data.models

import com.squareup.moshi.Json

// Request/Response classes
data class LoginRequest(
    val email: String,
    val password: String
)

data class RegisterRequest(
    val name: String,
    val email: String,
    val password: String
)

data class AuthResponse(
    val user: UserDto,
    val token: String
)

data class UserDto(
    val id: Long,
    val name: String,
    val email: String,
    val role: String,
    @Json(name = "prefeitura_id")
    val prefeituraId: Long? = null
)

// Prefeitura
data class PrefeituraDto(
    val id: Long,
    val nome: String,
    val slug: String? = null
)

// Problema
data class ProblemaDto(
    val id: Long,
    val titulo: String,
    val descricao: String,
    val bairro: String? = null,
    val rua: String? = null,
    val numero: String? = null,
    val cep: String? = null,
    val latitude: Double? = null,
    val longitude: Double? = null,
    val status: String = "aberto",
    @Json(name = "user_id")
    val userId: Long? = null,
    @Json(name = "prefeitura_id")
    val prefeituraId: Long? = null,
    @Json(name = "created_at")
    val createdAt: String? = null,
    @Json(name = "updated_at")
    val updatedAt: String? = null
)

data class ProblemaCreateRequest(
    val titulo: String,
    val descricao: String,
    val bairro: String?,
    val rua: String?,
    val numero: String?,
    val cep: String?,
    val latitude: Double?,
    val longitude: Double?,
    @Json(name = "prefeitura_id")
    val prefeituraId: Long?
)

// Geocode Response
data class ReverseGeocodeResponse(
    @Json(name = "address")
    val address: GeoAddress? = null
)

data class GeoAddress(
    val road: String? = null,
    val suburb: String? = null,
    val neighbourhood: String? = null,
    val city: String? = null,
    val state: String? = null,
    @Json(name = "postcode")
    val postcode: String? = null
)

// ViaCEP Response
data class ViaCepResponse(
    val cep: String? = null,
    @Json(name = "logradouro")
    val logradouro: String? = null,
    @Json(name = "bairro")
    val bairro: String? = null,
    @Json(name = "localidade")
    val localidade: String? = null,
    @Json(name = "uf")
    val uf: String? = null,
    val erro: Boolean? = null
)
