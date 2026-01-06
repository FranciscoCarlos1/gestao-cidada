package com.scs.gestaocidada.ui

import androidx.compose.foundation.layout.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import com.scs.gestaocidada.data.*
import kotlinx.coroutines.launch

@Composable
fun LoginScreen(onDone: () -> Unit) {
    val scope = rememberCoroutineScope()

    var email by remember { mutableStateOf("cidadao@demo.local") }
    var senha by remember { mutableStateOf("12345678") }
    var loading by remember { mutableStateOf(false) }
    var erro by remember { mutableStateOf<String?>(null) }

    Column(Modifier.fillMaxSize().padding(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
        Text("Gestão Cidadã", style = MaterialTheme.typography.headlineMedium)
        Text("Login", style = MaterialTheme.typography.titleMedium)

        OutlinedTextField(
            value = email,
            onValueChange = { email = it },
            label = { Text("E-mail") },
            modifier = Modifier.fillMaxWidth()
        )
        OutlinedTextField(
            value = senha,
            onValueChange = { senha = it },
            label = { Text("Senha") },
            modifier = Modifier.fillMaxWidth(),
            visualTransformation = PasswordVisualTransformation()
        )

        if (erro != null) {
            Text(erro!!, color = MaterialTheme.colorScheme.error)
        }

        Button(
            onClick = {
                loading = true
                erro = null
                scope.launch {
                    try {
                        val res = ApiClient.api.login(LoginRequest(email, senha))
                        Session.token = res.token
                        onDone()
                    } catch (e: Exception) {
                        erro = "Falha no login: ${e.message}"
                    } finally {
                        loading = false
                    }
                }
            },
            enabled = !loading,
            modifier = Modifier.fillMaxWidth()
        ) {
            Text(if (loading) "Entrando..." else "Entrar")
        }

        Text(
            "Usuários seed: cidadao@demo.local / 12345678",
            style = MaterialTheme.typography.bodySmall
        )
    }
}
