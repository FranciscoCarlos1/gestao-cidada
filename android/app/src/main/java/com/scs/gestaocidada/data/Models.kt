package com.scs.gestaocidada.data

data class UserDto(
    val id: Long,
    val name: String,
    val email: String,
    val role: String,
    val prefeitura_id: Long? = null
)

data class AuthResponse(
    val user: UserDto,
    val token: String
)

data class PrefeituraDto(
    val id: Long,
    val nome: String,
    val slug: String
)

data class ProblemaDto(
    val id: Long,
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
    val status: String,
    val prefeitura_id: Long,
    val user_id: Long? = null,
    val created_at: String? = null,
    val updated_at: String? = null
)

data class LoginRequest(val email: String, val password: String)
data class RegisterRequest(val name: String, val email: String, val password: String)

data class ProblemaCreateRequest(
    val titulo: String,
    val descricao: String,
    val bairro: String,
    val prefeitura_id: Long,
    val rua: String? = null,
    val numero: String? = null,
    val complemento: String? = null,
    val cep: String? = null,
    val cidade: String? = null,
    val uf: String? = null,
    val latitude: Double? = null,
    val longitude: Double? = null
)

data class ReverseGeocodeResponse(
    val bairro: String? = null,
    val rua: String? = null,
    val numero: String? = null,
    val cep: String? = null,
    val cidade: String? = null,
    val uf: String? = null,
    val display_name: String? = null
)

data class ViaCepResponse(
    val cep: String? = null,
    val logradouro: String? = null,
    val complemento: String? = null,
    val bairro: String? = null,
    val localidade: String? = null,
    val uf: String? = null,
    val ibge: String? = null,
    val gia: String? = null,
    val ddd: String? = null,
    val siafi: String? = null
)
