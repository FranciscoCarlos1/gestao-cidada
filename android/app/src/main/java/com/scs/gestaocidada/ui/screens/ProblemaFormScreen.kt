package com.scs.gestaocidada.ui.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import com.google.android.gms.maps.model.LatLng
import com.scs.gestaocidada.data.models.PrefeituraDto
import com.scs.gestaocidada.ui.viewmodels.ProblemaViewModel

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ProblemaFormScreen(
    selectedLocation: LatLng?,
    onNavigateBack: () -> Unit,
    viewModel: ProblemaViewModel = viewModel()
) {
    var titulo by remember { mutableStateOf("") }
    var descricao by remember { mutableStateOf("") }
    var bairro by remember { mutableStateOf("") }
    var rua by remember { mutableStateOf("") }
    var numero by remember { mutableStateOf("") }
    var cep by remember { mutableStateOf("") }
    var selectedPrefeitura by remember { mutableStateOf<PrefeituraDto?>(null) }
    var isLoading by remember { mutableStateOf(false) }
    var successMessage by remember { mutableStateOf<String?>(null) }
    var errorMessage by remember { mutableStateOf<String?>(null) }

    val prefeituras by viewModel.prefeituras.collectAsState()
    val isLoadingPrefeituras by viewModel.isLoadingPrefeituras.collectAsState()

    LaunchedEffect(Unit) {
        viewModel.loadPrefeituras()
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Reportar Problema") },
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
                .padding(16.dp)
                .verticalScroll(rememberScrollState())
        ) {
            if (selectedLocation != null) {
                Card(
                    modifier = Modifier.fillMaxWidth(),
                    colors = CardDefaults.cardColors(
                        containerColor = MaterialTheme.colorScheme.secondaryContainer
                    )
                ) {
                    Column(modifier = Modifier.padding(12.dp)) {
                        Text(
                            text = "📍 Localização marcada",
                            style = MaterialTheme.typography.labelMedium
                        )
                        Text(
                            text = "Lat: ${String.format("%.4f", selectedLocation.latitude)}, " +
                                    "Lng: ${String.format("%.4f", selectedLocation.longitude)}",
                            style = MaterialTheme.typography.bodySmall
                        )
                    }
                }
                Spacer(modifier = Modifier.height(16.dp))
            }

            OutlinedTextField(
                value = titulo,
                onValueChange = { titulo = it },
                label = { Text("Título *") },
                modifier = Modifier.fillMaxWidth(),
                enabled = !isLoading,
                singleLine = true
            )

            Spacer(modifier = Modifier.height(12.dp))

            OutlinedTextField(
                value = descricao,
                onValueChange = { descricao = it },
                label = { Text("Descrição *") },
                modifier = Modifier.fillMaxWidth(),
                enabled = !isLoading,
                minLines = 3,
                maxLines = 5
            )

            Spacer(modifier = Modifier.height(12.dp))

            OutlinedTextField(
                value = bairro,
                onValueChange = { bairro = it },
                label = { Text("Bairro *") },
                modifier = Modifier.fillMaxWidth(),
                enabled = !isLoading,
                singleLine = true
            )

            Spacer(modifier = Modifier.height(12.dp))

            Row(modifier = Modifier.fillMaxWidth()) {
                OutlinedTextField(
                    value = rua,
                    onValueChange = { rua = it },
                    label = { Text("Rua") },
                    modifier = Modifier.weight(2f),
                    enabled = !isLoading,
                    singleLine = true
                )
                Spacer(modifier = Modifier.width(8.dp))
                OutlinedTextField(
                    value = numero,
                    onValueChange = { numero = it },
                    label = { Text("Nº") },
                    modifier = Modifier.weight(1f),
                    enabled = !isLoading,
                    singleLine = true
                )
            }

            Spacer(modifier = Modifier.height(12.dp))

            OutlinedTextField(
                value = cep,
                onValueChange = { cep = it },
                label = { Text("CEP") },
                modifier = Modifier.fillMaxWidth(),
                enabled = !isLoading,
                singleLine = true,
                placeholder = { Text("00000-000") }
            )

            Spacer(modifier = Modifier.height(12.dp))

            if (isLoadingPrefeituras) {
                CircularProgressIndicator(modifier = Modifier.padding(16.dp))
            } else if (prefeituras.isEmpty()) {
                Text(
                    text = "Nenhuma prefeitura disponível",
                    color = MaterialTheme.colorScheme.error
                )
            } else {
                var expanded by remember { mutableStateOf(false) }
                
                ExposedDropdownMenuBox(
                    expanded = expanded,
                    onExpandedChange = { expanded = !expanded }
                ) {
                    OutlinedTextField(
                        value = selectedPrefeitura?.nome ?: "Selecione a Prefeitura *",
                        onValueChange = {},
                        readOnly = true,
                        label = { Text("Prefeitura") },
                        trailingIcon = { ExposedDropdownMenuDefaults.TrailingIcon(expanded = expanded) },
                        modifier = Modifier
                            .fillMaxWidth()
                            .menuAnchor(),
                        enabled = !isLoading
                    )

                    ExposedDropdownMenu(
                        expanded = expanded,
                        onDismissRequest = { expanded = false }
                    ) {
                        prefeituras.forEach { prefeitura ->
                            DropdownMenuItem(
                                text = { Text(prefeitura.nome) },
                                onClick = {
                                    selectedPrefeitura = prefeitura
                                    expanded = false
                                }
                            )
                        }
                    }
                }
            }

            Spacer(modifier = Modifier.height(24.dp))

            if (successMessage != null) {
                Card(
                    modifier = Modifier.fillMaxWidth(),
                    colors = CardDefaults.cardColors(
                        containerColor = MaterialTheme.colorScheme.primaryContainer
                    )
                ) {
                    Text(
                        text = successMessage!!,
                        modifier = Modifier.padding(16.dp),
                        color = MaterialTheme.colorScheme.onPrimaryContainer
                    )
                }
                Spacer(modifier = Modifier.height(16.dp))
            }

            if (errorMessage != null) {
                Card(
                    modifier = Modifier.fillMaxWidth(),
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
                Spacer(modifier = Modifier.height(16.dp))
            }

            Button(
                onClick = {
                    if (titulo.isBlank() || descricao.isBlank() || bairro.isBlank() || selectedPrefeitura == null) {
                        errorMessage = "Preencha todos os campos obrigatórios (*)"
                        return@Button
                    }

                    isLoading = true
                    errorMessage = null
                    successMessage = null

                    viewModel.submitProblema(
                        titulo = titulo,
                        descricao = descricao,
                        bairro = bairro,
                        rua = rua.ifBlank { null },
                        numero = numero.ifBlank { null },
                        cep = cep.ifBlank { null },
                        prefeituraId = selectedPrefeitura!!.id,
                        latitude = selectedLocation?.latitude,
                        longitude = selectedLocation?.longitude,
                        onSuccess = { protocolo ->
                            isLoading = false
                            successMessage = "✓ Protocolo #$protocolo criado com sucesso!"
                            titulo = ""
                            descricao = ""
                            bairro = ""
                            rua = ""
                            numero = ""
                            cep = ""
                        },
                        onError = { error ->
                            isLoading = false
                            errorMessage = error
                        }
                    )
                },
                modifier = Modifier.fillMaxWidth(),
                enabled = !isLoading
            ) {
                if (isLoading) {
                    CircularProgressIndicator(
                        modifier = Modifier.size(20.dp),
                        color = MaterialTheme.colorScheme.onPrimary
                    )
                    Spacer(modifier = Modifier.width(8.dp))
                }
                Text("Enviar Problema")
            }
        }
    }
}
