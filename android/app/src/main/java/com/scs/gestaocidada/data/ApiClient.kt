package com.scs.gestaocidada.data

import com.squareup.moshi.Moshi
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.moshi.MoshiConverterFactory

object ApiClient {
    private val logging = HttpLoggingInterceptor().apply {
        setLevel(HttpLoggingInterceptor.Level.BODY)
    }

    private val authInterceptor = okhttp3.Interceptor { chain ->
        val request = chain.request()
        val token = TokenManager.getToken()
        val newRequest = if (token != null) {
            request.newBuilder()
                .header("Authorization", "Bearer $token")
                .build()
        } else {
            request
        }
        chain.proceed(newRequest)
    }

    private val http = OkHttpClient.Builder()
        .addInterceptor(logging)
        .addInterceptor(authInterceptor)
        .build()

    private val moshi = Moshi.Builder().build()

    private val retrofit = Retrofit.Builder()
        .baseUrl(ApiConfig.BASE_URL)
        .client(http)
        .addConverterFactory(MoshiConverterFactory.create(moshi))
        .build()

    val authService: ApiService = retrofit.create(ApiService::class.java)
    val api: ApiService = retrofit.create(ApiService::class.java)
}
