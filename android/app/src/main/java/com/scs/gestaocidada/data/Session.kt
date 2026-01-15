package com.scs.gestaocidada.data

object Session {
    var token: String? = null
    fun authHeader(): String? = token?.let { "Bearer $it" }
}
