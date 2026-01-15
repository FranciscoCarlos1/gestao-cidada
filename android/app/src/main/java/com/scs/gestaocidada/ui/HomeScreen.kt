package com.scs.gestaocidada.ui

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.scs.gestaocidada.data.*
import kotlinx.coroutines.launch

@Composable
fun HomeScreen(onNovo: () -> Unit, onLogout: () -> Unit) {
    val scope = rememberCoroutineScope()
    var loading by remember { mutableStateOf(true) }
    var erro by remember { mutableStateOf<String?>(null) }
    var itens by remember { mutableStateOf<List<ProblemaDto>>(emptyList()) }

    fun carregar() {
        val auth = Session.authHeader()
        if (auth == null) {
            onLogout()
            return
        }
        loading = true
        erro = null
        scope.launch {
            try {
                itens = ApiClient.api.meusProblemas(auth)
            } catch (e: Exception) {
                erro = "Erro ao carregar: ${e.message}"
            } finally {
                loading = false
            }
        }
    }

    LaunchedEffect(Unit) { carregar() }

    Column(Modifier.fillMaxSize().padding(16.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            Button(onClick = onNovo, modifier = Modifier.weight(1f)) { Text("Novo problema") }
            OutlinedButton(
                onClick = {
                    val auth = Session.authHeader()
                    if (auth == null) { onLogout(); return@OutlinedButton }
                    scope.launch {
                        try { ApiClient.api.logout(auth) } catch (_: Exception) {}
                        Session.token = null
                        onLogout()
                    }
                },
                modifier = Modifier.weight(1f)
            ) { Text("Sair") }
        }

        if (loading) {
            CircularProgressIndicator()
        } else if (erro != null) {
            Text(erro!!, color = MaterialTheme.colorScheme.error)
            OutlinedButton(onClick = { carregar() }) { Text("Tentar de novo") }
        } else {
            Text("Meus problemas", style = MaterialTheme.typography.titleMedium)
            if (itens.isEmpty()) {
                Text("Nenhum registro ainda.")
            } else {
                LazyColumn(verticalArrangement = Arrangement.spacedBy(8.dp)) {
                    items(itens) { p ->
                        Card {
                            Column(Modifier.padding(12.dp), verticalArrangement = Arrangement.spacedBy(4.dp)) {
                                Text(p.titulo, style = MaterialTheme.typography.titleMedium)
                                Text(p.descricao, style = MaterialTheme.typography.bodyMedium)
                                Text("Status: ${p.status} • Bairro: ${p.bairro}", style = MaterialTheme.typography.bodySmall)
                                val addr = listOfNotNull(p.rua?.let { "Rua: $it" }, p.numero?.let { "Nº: $it" }, p.cep?.let { "CEP: $it" }).joinToString(" • ")
                                if (addr.isNotBlank()) Text(addr, style = MaterialTheme.typography.bodySmall)
                                val coord = if (p.latitude != null && p.longitude != null) "(${p.latitude}, ${p.longitude})" else ""
                                if (coord.isNotBlank()) Text(coord, style = MaterialTheme.typography.bodySmall)
                            }
                        }
                    }
                }
            }
        }
    }
}
