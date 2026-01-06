package com.scs.gestaocidada.ui.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import com.scs.gestaocidada.data.models.Problema
import com.scs.gestaocidada.ui.viewmodels.AdminViewModel
import java.text.SimpleDateFormat
import java.util.*

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun AdminScreen(
    onNavigateBack: () -> Unit,
    viewModel: AdminViewModel = viewModel()
) {
    val problemas by viewModel.problemas.collectAsState()
    val isLoading by viewModel.isLoading.collectAsState()
    val errorMessage by viewModel.errorMessage.collectAsState()
    var selectedFilter by remember { mutableStateOf("todos") }

    LaunchedEffect(selectedFilter) {
        viewModel.loadProblemas(
            status = if (selectedFilter == "todos") null else selectedFilter
        )
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Painel Administrativo") },
                navigationIcon = {
                    IconButton(onClick = onNavigateBack) {
                        Icon(Icons.Default.ArrowBack, contentDescription = "Voltar")
                    }
                },
                actions = {
                    IconButton(
                        onClick = {
                            viewModel.loadProblemas(
                                status = if (selectedFilter == "todos") null else selectedFilter
                            )
                        }
                    ) {
                        Icon(Icons.Default.Refresh, contentDescription = "Atualizar")
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
            // Filtros
            Card(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(16.dp)
            ) {
                Column(modifier = Modifier.padding(16.dp)) {
                    Text(
                        text = "Filtrar por status:",
                        style = MaterialTheme.typography.labelMedium
                    )
                    Spacer(modifier = Modifier.height(8.dp))
                    
                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.spacedBy(8.dp)
                    ) {
                        FilterChip(
                            selected = selectedFilter == "todos",
                            onClick = { selectedFilter = "todos" },
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
                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.spacedBy(8.dp)
                    ) {
                        FilterChip(
                            selected = selectedFilter == "resolvido",
                            onClick = { selectedFilter = "resolvido" },
                            label = { Text("Resolvido") }
                        )
                        FilterChip(
                            selected = selectedFilter == "rejeitado",
                            onClick = { selectedFilter = "rejeitado" },
                            label = { Text("Rejeitado") }
                        )
                    }
                }
            }

            Box(modifier = Modifier.fillMaxSize()) {
                when {
                    isLoading && problemas.isEmpty() -> {
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
                    problemas.isEmpty() -> {
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
                                text = "Nenhum problema encontrado",
                                style = MaterialTheme.typography.bodyLarge,
                                color = MaterialTheme.colorScheme.onSurfaceVariant
                            )
                        }
                    }
                    else -> {
                        LazyColumn(
                            modifier = Modifier.fillMaxSize(),
                            contentPadding = PaddingValues(horizontal = 16.dp, vertical = 8.dp),
                            verticalArrangement = Arrangement.spacedBy(12.dp)
                        ) {
                            items(problemas) { problema ->
                                AdminProblemaCard(
                                    problema = problema,
                                    onStatusChange = { newStatus ->
                                        viewModel.updateStatus(problema.id, newStatus)
                                    }
                                )
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun AdminProblemaCard(
    problema: Problema,
    onStatusChange: (String) -> Unit
) {
    val dateFormat = remember { SimpleDateFormat("dd/MM/yyyy HH:mm", Locale("pt", "BR")) }
    var showStatusDialog by remember { mutableStateOf(false) }
    
    val statusColor = when (problema.status) {
        "pendente" -> MaterialTheme.colorScheme.secondary
        "em_analise" -> MaterialTheme.colorScheme.tertiary
        "em_andamento" -> MaterialTheme.colorScheme.primary
        "resolvido" -> MaterialTheme.colorScheme.primaryContainer
        "rejeitado" -> MaterialTheme.colorScheme.error
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
                    color = statusColor.copy(alpha = 0.2f),
                    onClick = { showStatusDialog = true }
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
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )

            Spacer(modifier = Modifier.height(8.dp))

            Text(
                text = "📍 ${problema.bairro}${problema.cidade?.let { ", $it" } ?: ""}${problema.uf?.let { "/$it" } ?: ""}",
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )

            if (problema.rua != null || problema.numero != null) {
                Text(
                    text = "${problema.rua ?: ""}${problema.numero?.let { ", $it" } ?: ""}",
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
            }

            Spacer(modifier = Modifier.height(8.dp))

            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween
            ) {
                problema.user_id?.let {
                    Text(
                        text = "👤 Usuário #$it",
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }
                problema.created_at?.let { createdAt ->
                    Text(
                        text = dateFormat.format(createdAt),
                        style = MaterialTheme.typography.bodySmall,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }
            }

            if (problema.latitude != null && problema.longitude != null) {
                Spacer(modifier = Modifier.height(4.dp))
                Text(
                    text = "🗺️ ${String.format("%.4f", problema.latitude)}, ${String.format("%.4f", problema.longitude)}",
                    style = MaterialTheme.typography.bodySmall,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
            }
        }
    }

    if (showStatusDialog) {
        AlertDialog(
            onDismissRequest = { showStatusDialog = false },
            title = { Text("Alterar Status") },
            text = {
                Column {
                    listOf(
                        "pendente" to "Pendente",
                        "em_analise" to "Em Análise",
                        "em_andamento" to "Em Andamento",
                        "resolvido" to "Resolvido",
                        "rejeitado" to "Rejeitado"
                    ).forEach { (value, label) ->
                        TextButton(
                            onClick = {
                                onStatusChange(value)
                                showStatusDialog = false
                            },
                            modifier = Modifier.fillMaxWidth()
                        ) {
                            Text(label)
                        }
                    }
                }
            },
            confirmButton = {},
            dismissButton = {
                TextButton(onClick = { showStatusDialog = false }) {
                    Text("Cancelar")
                }
            }
        )
    }
}
