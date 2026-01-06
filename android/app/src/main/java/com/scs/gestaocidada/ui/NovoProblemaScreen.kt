package com.scs.gestaocidada.ui

import android.Manifest
import android.annotation.SuppressLint
import android.content.pm.PackageManager
import android.location.Location
import androidx.activity.compose.rememberLauncherForActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.foundation.layout.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.unit.dp
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Map
import androidx.compose.material.icons.filled.Search
import androidx.core.content.ContextCompat
import com.google.android.gms.location.FusedLocationProviderClient
import com.google.android.gms.location.LocationServices
import com.scs.gestaocidada.data.*
import kotlinx.coroutines.launch
import kotlin.coroutines.resume
import kotlinx.coroutines.suspendCancellableCoroutine

@Composable
fun NovoProblemaScreen(onDone: () -> Unit) {
    val scope = rememberCoroutineScope()
    val ctx = LocalContext.current
    val fused = remember { LocationServices.getFusedLocationProviderClient(ctx) }

    var titulo by remember { mutableStateOf("") }
    var descricao by remember { mutableStateOf("") }

    // endereço (preenchido manualmente ou via geolocalização)
    var bairro by remember { mutableStateOf("") }
    var rua by remember { mutableStateOf("") }
    var numero by remember { mutableStateOf("") }
    var cep by remember { mutableStateOf("") }
    var cidade by remember { mutableStateOf("") }
    var uf by remember { mutableStateOf("") }

    var lat by remember { mutableStateOf<Double?>(null) }
    var lng by remember { mutableStateOf<Double?>(null) }

    var showMap by remember { mutableStateOf(false) }

    var prefeituraId by remember { mutableStateOf("1") } // seed SBS = 1
    var loading by remember { mutableStateOf(false) }
    var erro by remember { mutableStateOf<String?>(null) }
    var infoGeo by remember { mutableStateOf<String?>(null) }

    var showMap by remember { mutableStateOf(false) }

    fun aplicarAddr(a: ReverseGeocodeResponse) {
        if (bairro.isBlank() && !a.bairro.isNullOrBlank()) bairro = a.bairro!!
        if (rua.isBlank() && !a.rua.isNullOrBlank()) rua = a.rua!!
        if (numero.isBlank() && !a.numero.isNullOrBlank()) numero = a.numero!!
        if (cep.isBlank() && !a.cep.isNullOrBlank()) cep = a.cep!!
        if (cidade.isBlank() && !a.cidade.isNullOrBlank()) cidade = a.cidade!!
        if (uf.isBlank() && !a.uf.isNullOrBlank()) uf = a.uf!!
        infoGeo = a.display_name
    }

    fun aplicarViaCep(v: ViaCepResponse) {
        // ViaCEP não traz número; apenas rua/bairro/cidade/uf
        if (rua.isBlank() && !v.logradouro.isNullOrBlank()) rua = v.logradouro!!
        if (bairro.isBlank() && !v.bairro.isNullOrBlank()) bairro = v.bairro!!
        if (cidade.isBlank() && !v.localidade.isNullOrBlank()) cidade = v.localidade!!
        if (uf.isBlank() && !v.uf.isNullOrBlank()) uf = v.uf!!
        if (cep.isBlank() && !v.cep.isNullOrBlank()) cep = v.cep!!.replace("-", "")
    }

    val permissionLauncher = rememberLauncherForActivityResult(
        contract = ActivityResultContracts.RequestPermission()
    ) { granted ->
        if (granted) {
            scope.launch {
                preencherEnderecoPorLocalizacao(
                    fused = fused,
                    onCoords = { la, lo -> lat = la; lng = lo },
                    onAddr = { aplicarAddr(it) },
                    onError = { erro = it }
                )
            }
        } else {
            erro = "Permissão de localização negada."
        }
    }

    Column(Modifier.fillMaxSize().padding(16.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
        Text("Novo problema", style = MaterialTheme.typography.headlineSmall)

        OutlinedTextField(
            value = titulo,
            onValueChange = { titulo = it },
            label = { Text("Título") },
            modifier = Modifier.fillMaxWidth()
        )
        OutlinedTextField(
            value = descricao,
            onValueChange = { descricao = it },
            label = { Text("Descrição") },
            modifier = Modifier.fillMaxWidth(),
            minLines = 3
        )

        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            OutlinedButton(
                onClick = {
                    val granted = ContextCompat.checkSelfPermission(ctx, Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED
                    if (granted) {
                        scope.launch {
                            preencherEnderecoPorLocalizacao(
                                fused = fused,
                                onCoords = { la, lo -> lat = la; lng = lo },
                                onAddr = { aplicarAddr(it) },
                                onError = { erro = it }
                            )
                        }
                    } else {
                        permissionLauncher.launch(Manifest.permission.ACCESS_FINE_LOCATION)
                    }
                }
            ) { Text("Usar minha localização") }

            OutlinedButton(
                onClick = { showMap = true }
            ) {
                Icon(Icons.Filled.Map, contentDescription = null)
                Spacer(Modifier.width(8.dp))
                Text("Selecionar no mapa")
            }

            if (lat != null && lng != null) {
                Text(
                    "OK (${String.format("%.5f", lat)} , ${String.format("%.5f", lng)})",
                    style = MaterialTheme.typography.bodySmall
                )
            }
        }

        MapPickerDialog(
            show = showMap,
            initialLat = lat,
            initialLng = lng,
            fusedLocationClient = fused,
            onDismiss = { showMap = false },
            onConfirm = { la, lo ->
                showMap = false
                lat = la
                lng = lo
                scope.launch {
                    try {
                        val addr = ApiClient.api.reverseGeocode(la, lo)
                        aplicarAddr(addr)
                    } catch (e: Exception) {
                        erro = "Falha ao obter endereço do mapa: ${e.message}"
                    }
                }
            }
        )

        if (!infoGeo.isNullOrBlank()) {
            Text("Local detectado: $infoGeo", style = MaterialTheme.typography.bodySmall)
        }

        Text("Endereço", style = MaterialTheme.typography.titleMedium)

        OutlinedTextField(value = bairro, onValueChange = { bairro = it }, label = { Text("Bairro *") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(value = rua, onValueChange = { rua = it }, label = { Text("Rua") }, modifier = Modifier.fillMaxWidth())
        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            OutlinedTextField(value = numero, onValueChange = { numero = it }, label = { Text("Número") }, modifier = Modifier.weight(1f))
            OutlinedTextField(
                value = cep,
                onValueChange = { cep = it },
                label = { Text("CEP") },
                modifier = Modifier.weight(1f),
                trailingIcon = {
                    IconButton(
                        onClick = {
                            val cleaned = cep.replace(Regex("\\D"), "")
                            if (cleaned.length == 8) {
                                scope.launch {
                                    try {
                                        val r = ApiClient.api.consultarCep(cleaned)
                                        // Preenche (mantém o que o usuário já digitou se quiser)
                                        if (rua.isBlank() && !r.logradouro.isNullOrBlank()) rua = r.logradouro!!
                                        if (bairro.isBlank() && !r.bairro.isNullOrBlank()) bairro = r.bairro!!
                                        if (cidade.isBlank() && !r.localidade.isNullOrBlank()) cidade = r.localidade!!
                                        if (uf.isBlank() && !r.uf.isNullOrBlank()) uf = r.uf!!
                                        infoGeo = "CEP: ${r.cep ?: cleaned}"
                                    } catch (e: Exception) {
                                        erro = "Falha ao consultar CEP: ${e.message}"
                                    }
                                }
                            } else {
                                erro = "Informe um CEP com 8 dígitos para consultar."
                            }
                        }
                    ) {
                        Icon(Icons.Filled.Search, contentDescription = "Consultar CEP")
                    }
                }
            )
        }
        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            OutlinedTextField(value = cidade, onValueChange = { cidade = it }, label = { Text("Cidade") }, modifier = Modifier.weight(1f))
            OutlinedTextField(value = uf, onValueChange = { uf = it }, label = { Text("UF") }, modifier = Modifier.width(90.dp))
        }

        OutlinedTextField(
            value = prefeituraId,
            onValueChange = { prefeituraId = it },
            label = { Text("Prefeitura ID") },
            supportingText = { Text("Seed: SBS = 1") },
            modifier = Modifier.fillMaxWidth()
        )

        if (erro != null) {
            Text(erro!!, color = MaterialTheme.colorScheme.error)
        }

        Button(
            onClick = {
                val token = Session.authHeader()
                loading = true
                erro = null
                scope.launch {
                    try {
                        ApiClient.api.criarProblema(
                            ProblemaCreateRequest(
                                titulo = titulo,
                                descricao = descricao,
                                bairro = bairro,
                                rua = rua.ifBlank { null },
                                numero = numero.ifBlank { null },
                                cep = cep.ifBlank { null },
                                cidade = cidade.ifBlank { null },
                                uf = uf.ifBlank { null },
                                prefeitura_id = prefeituraId.toLong(),
                                latitude = lat,
                                longitude = lng,
                            ),
                            token
                        )
                        onDone()
                    } catch (e: Exception) {
                        erro = "Erro ao enviar: ${e.message}"
                    } finally {
                        loading = false
                    }
                }
            },
            enabled = !loading,
            modifier = Modifier.fillMaxWidth()
        ) {
            Text(if (loading) "Enviando..." else "Registrar")
        }
    }
}

@SuppressLint("MissingPermission")
private suspend fun preencherEnderecoPorLocalizacao(
    fused: FusedLocationProviderClient,
    onCoords: (Double, Double) -> Unit,
    onAddr: (ReverseGeocodeResponse) -> Unit,
    onError: (String) -> Unit
) {
    try {
        val loc: Location? = fused.lastLocationAwait()
        if (loc == null) {
            onError("Não foi possível obter localização. Ative o GPS e tente novamente.")
            return
        }

        val lat = loc.latitude
        val lng = loc.longitude
        onCoords(lat, lng)

        val addr = ApiClient.api.reverseGeocode(lat, lng)
        onAddr(addr)
    } catch (e: Exception) {
        onError("Falha ao obter endereço: ${e.message}")
    }
}

private suspend fun FusedLocationProviderClient.lastLocationAwait(): Location? {
    return suspendCancellableCoroutine { cont ->
        lastLocation
            .addOnSuccessListener { cont.resume(it) }
            .addOnFailureListener { cont.resume(null) }
    }
}
