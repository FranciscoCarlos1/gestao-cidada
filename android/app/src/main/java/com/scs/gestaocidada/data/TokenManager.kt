package com.scs.gestaocidada.data

import android.content.Context
import android.content.SharedPreferences

object TokenManager {
    private lateinit var prefs: SharedPreferences
    private const val PREF_NAME = "gestaocidada_prefs"
    private const val TOKEN_KEY = "auth_token"
    private const val ROLE_KEY = "user_role"
    private const val USER_ID_KEY = "user_id"
    private const val USER_NAME_KEY = "user_name"
    private const val USER_EMAIL_KEY = "user_email"

    fun init(context: Context) {
        prefs = context.getSharedPreferences(PREF_NAME, Context.MODE_PRIVATE)
    }

    fun saveToken(token: String) {
        prefs.edit().putString(TOKEN_KEY, token).apply()
    }

    fun getToken(): String? = prefs.getString(TOKEN_KEY, null)

    fun saveUserRole(role: String) {
        prefs.edit().putString(ROLE_KEY, role).apply()
    }

    fun getUserRole(): String? = prefs.getString(ROLE_KEY, null)

    fun saveUserInfo(userId: Long, name: String, email: String) {
        prefs.edit().apply {
            putLong(USER_ID_KEY, userId)
            putString(USER_NAME_KEY, name)
            putString(USER_EMAIL_KEY, email)
        }.apply()
    }

    fun getUserId(): Long = prefs.getLong(USER_ID_KEY, -1)

    fun getUserName(): String? = prefs.getString(USER_NAME_KEY, null)

    fun getUserEmail(): String? = prefs.getString(USER_EMAIL_KEY, null)

    fun clearToken() {
        prefs.edit().remove(TOKEN_KEY).apply()
    }

    fun clearUserRole() {
        prefs.edit().remove(ROLE_KEY).apply()
    }

    fun clearAll() {
        prefs.edit().clear().apply()
    }

    fun isLoggedIn(): Boolean = !getToken().isNullOrEmpty()

    fun getAuthHeader(): String? {
        return getToken()?.let { "Bearer $it" }
    }
}
