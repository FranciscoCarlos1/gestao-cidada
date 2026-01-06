package com.scs.gestaocidada.ui.screens

import androidx.compose.foundation.layout.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun AboutScreen(
    onNavigateBack: () -> Unit
) {
    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Sobre o Projeto") },
                navigationIcon = {
                    IconButton(onClick = onNavigateBack) {
                        Icon(Icons.Default.ArrowBack, contentDescription = "Voltar")
                    }
                }
            )
        }
    ) { paddingValues ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(paddingValues)
                .padding(16.dp),
            verticalArrangement = Arrangement.spacedBy(12.dp)
        ) {
            Text(
                text = "Gestão Cidadã",
                style = MaterialTheme.typography.headlineSmall,
                fontWeight = FontWeight.SemiBold
            )
            Text(
                text = "Projeto feito e desenvolvido por",
                style = MaterialTheme.typography.bodyMedium,
            )
            Text(
                text = "FRANCISCO CARLOS DE SOUSA",
                style = MaterialTheme.typography.titleMedium,
                fontWeight = FontWeight.Bold
            )
            Text(
                text = "Analista de Sistema pela Estácio",
                style = MaterialTheme.typography.bodyMedium,
            )
            Text(
                text = "Servidor Público: Técnico de Tecnologia da Informação no IFC - São Bento do Sul",
                style = MaterialTheme.typography.bodyMedium,
            )
            Divider()
            Text(
                text = "Este aplicativo conecta cidadãos e prefeituras para reportar, acompanhar e administrar problemas urbanos de forma prática.",
                style = MaterialTheme.typography.bodyMedium
            )
        }
    }
}
