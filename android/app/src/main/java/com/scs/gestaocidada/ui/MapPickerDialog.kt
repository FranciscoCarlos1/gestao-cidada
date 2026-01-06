package com.scs.gestaocidada.ui

import android.content.Context
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.size
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.unit.dp
import androidx.compose.ui.viewinterop.AndroidView
import androidx.compose.ui.Alignment
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.res.painterResource
import kotlinx.coroutines.suspendCancellableCoroutine
import kotlin.coroutines.resume
import org.osmdroid.config.Configuration
import org.osmdroid.tileprovider.tilesource.TileSourceFactory
import org.osmdroid.util.GeoPoint
import org.osmdroid.views.MapView
import org.osmdroid.views.overlay.Marker
import org.osmdroid.events.MapListener
import org.osmdroid.events.ScrollEvent
import org.osmdroid.events.ZoomEvent

/**
 * Seletor de local no mapa (OSM/OSMDroid).
 *
 * UX: o usuário move o mapa (pin fica no centro). Ao confirmar, retorna lat/lng do centro.
 */
@Composable
fun MapPickerDialog(
    show: Boolean,
    initialLat: Double? = null,
    initialLng: Double? = null,
    fusedLocationClient: com.google.android.gms.location.FusedLocationProviderClient? = null,
    onDismiss: () -> Unit,
    onConfirm: (Double, Double) -> Unit,
) {
    if (!show) return

    val ctx = LocalContext.current

    // Config do osmdroid (importante definir userAgent)
    LaunchedEffect(Unit) {
        configureOsmdroid(ctx)
    }

    var centerLat by remember { mutableStateOf(initialLat ?: -26.2493) } // SBS aprox.
    var centerLng by remember { mutableStateOf(initialLng ?: -49.3831) }

    AlertDialog(
        onDismissRequest = onDismiss,
        confirmButton = {
            Button(onClick = { onConfirm(centerLat, centerLng) }) {
                Text("Usar este local")
            }
        },
        dismissButton = {
            OutlinedButton(onClick = onDismiss) { Text("Cancelar") }
        },
        title = { Text("Selecione no mapa") },
        text = {
            Column(Modifier.fillMaxWidth(), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                Text(
                    "Arraste o mapa para posicionar o local. O ponto selecionado é o centro.",
                    style = MaterialTheme.typography.bodySmall
                )

                Box(modifier = Modifier.fillMaxWidth().height(360.dp)) {
                    AndroidView(
                        modifier = Modifier
                            .fillMaxSize(),
                        factory = {
                            MapView(it).apply {
                                setTileSource(TileSourceFactory.MAPNIK)
                                setMultiTouchControls(true)

                                val start = GeoPoint(centerLat, centerLng)
                                controller.setZoom(17.0)
                                controller.setCenter(start)

                                // Marker overlay (seguirá o centro do mapa)
                                val marker = Marker(this).apply {
                                    position = start
                                    setAnchor(Marker.ANCHOR_CENTER, Marker.ANCHOR_BOTTOM)
                                    title = "Local selecionado"
                                }
                                overlays.add(marker)

                                addMapListener(object : MapListener {
                                    override fun onScroll(event: ScrollEvent?): Boolean {
                                        val c = mapCenter as GeoPoint
                                        centerLat = c.latitude
                                        centerLng = c.longitude
                                        marker.position = GeoPoint(centerLat, centerLng)
                                        invalidate()
                                        return true
                                    }

                                    override fun onZoom(event: ZoomEvent?): Boolean {
                                        val c = mapCenter as GeoPoint
                                        centerLat = c.latitude
                                        centerLng = c.longitude
                                        marker.position = GeoPoint(centerLat, centerLng)
                                        invalidate()
                                        return true
                                    }
                                })
                            }
                        },
                        update = { map ->
                            // se os valores iniciais mudarem
                            val p = GeoPoint(centerLat, centerLng)
                            map.controller.setCenter(p)
                        }
                    )

                    // PIN fixo desenhado sobre o mapa (compose) — sempre no centro
                    Icon(
                        painter = painterResource(android.R.drawable.ic_menu_mylocation),
                        contentDescription = null,
                        tint = Color.Red,
                        modifier = Modifier
                            .size(48.dp)
                            .align(Alignment.Center)
                    )

                    // Botão para centralizar na localização do dispositivo
                    if (fusedLocationClient != null) {
                        FloatingActionButton(
                            onClick = {
                                // tenta obter a última localização e centralizar
                                // lançamos um efeito para usar coroutines
                                LaunchedEffect(Unit) {
                                    try {
                                        val loc = suspendCancellableCoroutine<android.location.Location?> { cont ->
                                            fusedLocationClient.lastLocation
                                                .addOnSuccessListener { cont.resume(it) }
                                                .addOnFailureListener { cont.resume(null) }
                                        }
                                        if (loc != null) {
                                            centerLat = loc.latitude
                                            centerLng = loc.longitude
                                        }
                                    } catch (_: Exception) {
                                        // ignorar: fallback silencioso
                                    }
                                }
                            },
                            modifier = Modifier
                                .align(Alignment.TopEnd)
                                .padding(8.dp)
                                .size(44.dp)
                        ) {
                            Icon(painter = painterResource(android.R.drawable.ic_menu_mylocation), contentDescription = "Centralizar")
                        }
                    }
                }

                Text(
                    "Lat/Lng: ${String.format("%.5f", centerLat)} , ${String.format("%.5f", centerLng)}",
                    style = MaterialTheme.typography.bodySmall
                )
            }
        }
    )
}

private fun configureOsmdroid(ctx: Context) {
    val conf = Configuration.getInstance()
    conf.userAgentValue = ctx.packageName
    conf.load(ctx, ctx.getSharedPreferences("osmdroid", Context.MODE_PRIVATE))
}
