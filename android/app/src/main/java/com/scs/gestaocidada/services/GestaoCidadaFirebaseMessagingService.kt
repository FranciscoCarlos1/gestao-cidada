package com.scs.gestaocidada.services

import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.os.Build
import androidx.core.app.NotificationCompat
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage
import com.scs.gestaocidada.MainActivity
import com.scs.gestaocidada.R
import com.scs.gestaocidada.data.PreferencesManager
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch

class GestaoCidadaFirebaseMessagingService : FirebaseMessagingService() {
    
    override fun onNewToken(token: String) {
        super.onNewToken(token)
        
        // Salvar token no DataStore
        CoroutineScope(Dispatchers.IO).launch {
            PreferencesManager.getInstance(applicationContext).setFcmToken(token)
        }
        
        // Aqui você pode enviar o token para o backend
        // ApiClient.api.updateFcmToken(token)
    }
    
    override fun onMessageReceived(message: RemoteMessage) {
        super.onMessageReceived(message)
        
        message.notification?.let {
            showNotification(
                title = it.title ?: "Gestão Cidadã",
                body = it.body ?: ""
            )
        }
        
        // Processar data payload se houver
        message.data.isNotEmpty().let {
            // Processar dados customizados
        }
    }
    
    private fun showNotification(title: String, body: String) {
        val channelId = "gestaocidada_channel"
        val notificationManager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
        
        // Criar canal de notificação (Android 8.0+)
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                channelId,
                "Gestão Cidadã Notifications",
                NotificationManager.IMPORTANCE_HIGH
            ).apply {
                description = "Notificações sobre problemas reportados"
                enableLights(true)
                enableVibration(true)
            }
            notificationManager.createNotificationChannel(channel)
        }
        
        // Intent para abrir o app ao clicar na notificação
        val intent = Intent(this, MainActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
        }
        val pendingIntent = PendingIntent.getActivity(
            this,
            0,
            intent,
            PendingIntent.FLAG_IMMUTABLE
        )
        
        // Construir notificação
        val notification = NotificationCompat.Builder(this, channelId)
            .setContentTitle(title)
            .setContentText(body)
            .setSmallIcon(android.R.drawable.ic_dialog_info) // Usar um ícone customizado
            .setAutoCancel(true)
            .setContentIntent(pendingIntent)
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .build()
        
        notificationManager.notify(System.currentTimeMillis().toInt(), notification)
    }
}
