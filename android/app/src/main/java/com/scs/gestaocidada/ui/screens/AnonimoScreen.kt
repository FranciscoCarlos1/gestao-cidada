package com.scs.gestaocidada.ui.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Login
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import com.scs.gestaocidada.data.models.ProblemaDto
import com.scs.gestaocidada.ui.viewmodels.ProblemaViewModel
import java.text.SimpleDateFormat
import java.util.*

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun AnonimoScreen(
    onNavigateToLogin: () -> Unit,
    viewModel: ProblemaViewModel = viewModel()
) {
    val problemasPublicos by viewModel.problemasPublicos.collectAsState()
    val isLoading by viewModel.isLoading.collectAsState()
    val errorMessage by viewModel.errorMessage.collectAsState()
    
    var selectedFilter by remember { mutableStateOf<String?>(null) }

    LaunchedEffect(selectedFilter) {
        viewModel.loadProblemasPublicos(status = selectedFilter)
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Problemas Públicos") },
                actions = {
                    IconButton(onClick = { viewModel.loadProblemasPublicos(status = selectedFilter) }) {
                        Icon(Icons.Default.Refresh, contentDescription = "Atualizar")
                    }
                    TextButton(onClick = onNavigateToLogin) {
                        Icon(Icons.Default.Login, contentDescription = "Login")
                        Spacer(modifier = Modifier.width(4.dp))
                        Text("Entrar")
                    }
                }
            )
        }
    ) { paddingValues ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(paddingValues)
        ) {
            // Info para visitante
            Card(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(16.dp),
                colors = CardDefaults.cardColors(
                    containerColor = MaterialTheme.colorScheme.secondaryContainer
                )
            ) {
                Column(modifier = Modifier.padding(16.dp)) {
                    Text(
                        text = "👋 Visitando como Anônimo",
                        style = MaterialTheme.typography.titleMedium,
                        color = MaterialTheme.colorScheme.onSecondaryContainer
                    )
                    Spacer(modifier = Modifier.height(8.dp))
                    Text(
                        text = "Você está visualizando problemas públicos. Faça login para reportar problemas e acompanhar seus reports.",
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSecondaryContainer
                    )
                }
            }

            // Filtros simples
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 16.dp),
                horizontalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                FilterChip(
                    selected = selectedFilter == null,
                    onClick = { selectedFilter = null },
                    label = { Text("Todos") }
                )
                FilterChip(
                    selected = selectedFilter == "pendente",
                    onClick = { selectedFilter = "pendente" },
                    label = { Text("Pendente") }
                )
                FilterChip(
                    selected = selectedFilter == "em_andamento",
                    onClick = { selectedFilter = "em_andamento" },
                    label = { Text("Em andamento") }
                )
            }

            Spacer(modifier = Modifier.height(8.dp))

            // Lista de problemas
            Box(modifier = Modifier.fillMaxSize()) {
                when {
                    isLoading && problemasPublicos.isEmpty() -> {
                        CircularProgressIndicator(
                            modifier = Modifier.align(Alignment.Center)
                        )
                    }
                    errorMessage != null -> {
                        Card(
                            modifier = Modifier
                                .align(Alignment.Center)
                                .padding(16.dp),
                            colors = CardDefaults.cardColors(
                                containerColor = MaterialTheme.colorScheme.errorContainer
                            )
                        ) {
                            Text(
                                text = errorMessage!!,
                                modifier = Modifier.padding(16.dp),
                                color = MaterialTheme.colorScheme.onErrorContainer
                            )
                        }
                    }
                    problemasPublicos.isEmpty() -> {
                        Column(
                            modifier = Modifier.align(Alignment.Center),
                            horizontalAlignment = Alignment.CenterHorizontally
                        ) {
                            Text(
                                text = "📋",
                                style = MaterialTheme.typography.displayLarge
                            )
                            Spacer(modifier = Modifier.height(16.dp))
                            Text(
                                text = "Nenhum problema público encontrado",
                                style = MaterialTheme.typography.bodyLarge,
                                color = MaterialTheme.colorScheme.onSurfaceVariant
                            )
                        }
                    }
                    else -> {
                        LazyColumn(
                            modifier = Modifier.fillMaxSize(),
                            contentPadding = PaddingValues(16.dp),
                            verticalArrangement = Arrangement.spacedBy(12.dp)
                        ) {
                            items(problemasPublicos) { problema ->
                                PublicProblemaCard(problema)
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun PublicProblemaCard(problema: ProblemaDto) {
    val dateFormat = remember { SimpleDateFormat("dd/MM/yyyy", Locale("pt", "BR")) }
    
    val statusColor = when (problema.status) {
        "pendente" -> MaterialTheme.colorScheme.secondary
        "em_analise" -> MaterialTheme.colorScheme.tertiary
        "em_andamento" -> MaterialTheme.colorScheme.primary
        "resolvido" -> MaterialTheme.colorScheme.primaryContainer
        else -> MaterialTheme.colorScheme.outline
    }

    Card(
        modifier = Modifier.fillMaxWidth(),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(modifier = Modifier.padding(16.dp)) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = "#${problema.id}",
                    style = MaterialTheme.typography.labelSmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
                Surface(
                    shape = MaterialTheme.shapes.small,
                    color = statusColor.copy(alpha = 0.2f)
                ) {
                    Text(
                        text = problema.status.replace("_", " ").uppercase(),
                        modifier = Modifier.padding(horizontal = 8.dp, vertical = 4.dp),
                        style = MaterialTheme.typography.labelSmall,
                        color = statusColor
                    )
                }
            }

            Spacer(modifier = Modifier.height(8.dp))

            Text(
                text = problema.titulo,
                style = MaterialTheme.typography.titleMedium,
                color = MaterialTheme.colorScheme.onSurface
            )

            Spacer(modifier = Modifier.height(4.dp))

            Text(
                text = problema.descricao,
                style = MaterialTheme.typography.bodyMedium,
                color = MaterialTheme.colorScheme.onSurfaceVariant,
                maxLines = 3
            )

            Spacer(modifier = Modifier.height(8.dp))

            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween
            ) {
                Column {
                    Text(
                        text = "📍 ${problema.bairro ?: ""}${problema.cidade?.let { ", $it" } ?: ""}",
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                    problema.prefeitura?.let {
                        Text(
                            text = "🏛️ ${it.nome}",
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                    }
                }
                problema.createdAt?.let { createdAt ->
                    Text(
                        text = createdAt.substring(0, 10.coerceAtMost(createdAt.length)), // Apenas a data
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }
            }
        }
    }
}
