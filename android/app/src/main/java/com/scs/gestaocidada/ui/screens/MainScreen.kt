package com.scs.gestaocidada.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.List
import androidx.compose.material.icons.filled.Settings
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import com.google.android.gms.maps.model.CameraPosition
import com.google.android.gms.maps.model.LatLng
import com.google.maps.android.compose.*
import com.scs.gestaocidada.ui.viewmodels.MapViewModel

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MainScreen(
    onNavigateToForm: (LatLng?) -> Unit,
    onNavigateToList: () -> Unit,
    onNavigateToAdmin: () -> Unit,
    onLogout: () -> Unit,
    userRole: String,
    viewModel: MapViewModel = viewModel()
) {
    var selectedLocation by remember { mutableStateOf<LatLng?>(null) }
    val defaultPosition = LatLng(-23.5505, -46.6333) // São Paulo

    val cameraPositionState = rememberCameraPositionState {
        position = CameraPosition.fromLatLngZoom(defaultPosition, 12f)
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Mapa - Toque para marcar local") },
                actions = {
                    if (userRole == "admin" || userRole == "super") {
                        IconButton(onClick = onNavigateToAdmin) {
                            Icon(Icons.Default.Settings, contentDescription = "Admin")
                        }
                    }
                    IconButton(onClick = onNavigateToList) {
                        Icon(Icons.Default.List, contentDescription = "Meus Problemas")
                    }
                    TextButton(onClick = onLogout) {
                        Text("Sair")
                    }
                }
            )
        },
        floatingActionButton = {
            FloatingActionButton(
                onClick = { onNavigateToForm(selectedLocation) }
            ) {
                Icon(Icons.Default.Add, contentDescription = "Reportar Problema")
            }
        }
    ) { paddingValues ->
        Box(modifier = Modifier
            .fillMaxSize()
            .padding(paddingValues)
        ) {
            GoogleMap(
                modifier = Modifier.fillMaxSize(),
                cameraPositionState = cameraPositionState,
                onMapClick = { latLng ->
                    selectedLocation = latLng
                }
            ) {
                selectedLocation?.let { location ->
                    Marker(
                        state = MarkerState(position = location),
                        title = "Local selecionado"
                    )
                }
            }

            if (selectedLocation != null) {
                Card(
                    modifier = Modifier
                        .align(Alignment.BottomCenter)
                        .padding(16.dp)
                        .fillMaxWidth(),
                    colors = CardDefaults.cardColors(
                        containerColor = MaterialTheme.colorScheme.primaryContainer
                    )
                ) {
                    Column(modifier = Modifier.padding(16.dp)) {
                        Text(
                            text = "Local marcado!",
                            style = MaterialTheme.typography.titleMedium
                        )
                        Text(
                            text = "Lat: ${String.format("%.4f", selectedLocation!!.latitude)}, " +
                                    "Lng: ${String.format("%.4f", selectedLocation!!.longitude)}",
                            style = MaterialTheme.typography.bodySmall
                        )
                        Spacer(modifier = Modifier.height(8.dp))
                        Text(
                            text = "Toque no botão + para reportar problema neste local",
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.onPrimaryContainer
                        )
                    }
                }
            }
        }
    }
}
