package com.scs.gestaocidada.ui.screens

import androidx.compose.foundation.Image
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.asImageBitmap
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import com.scs.gestaocidada.ui.viewmodels.TwoFactorViewModel

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun TwoFactorScreen(
    onNavigateBack: () -> Unit,
    viewModel: TwoFactorViewModel = viewModel()
) {
    val qrCodeBitmap by viewModel.qrCodeBitmap.collectAsState()
    val secret by viewModel.secret.collectAsState()
    val backupCodes by viewModel.backupCodes.collectAsState()
    val isEnabled by viewModel.isEnabled.collectAsState()
    val isLoading by viewModel.isLoading.collectAsState()
    val errorMessage by viewModel.errorMessage.collectAsState()
    val successMessage by viewModel.successMessage.collectAsState()
    
    var confirmCode by remember { mutableStateOf("") }
    var showConfirmDialog by remember { mutableStateOf(false) }
    var showDisableDialog by remember { mutableStateOf(false) }

    LaunchedEffect(Unit) {
        viewModel.check2FAStatus()
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Autenticação em 2 Fatores") },
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
                .verticalScroll(rememberScrollState())
                .padding(16.dp),
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.spacedBy(16.dp)
        ) {
            // Mensagens
            successMessage?.let {
                Card(
                    modifier = Modifier.fillMaxWidth(),
                    colors = CardDefaults.cardColors(
                        containerColor = MaterialTheme.colorScheme.primaryContainer
                    )
                ) {
                    Text(
                        text = it,
                        modifier = Modifier.padding(16.dp),
                        color = MaterialTheme.colorScheme.onPrimaryContainer
                    )
                }
            }
            
            errorMessage?.let {
                Card(
                    modifier = Modifier.fillMaxWidth(),
                    colors = CardDefaults.cardColors(
                        containerColor = MaterialTheme.colorScheme.errorContainer
                    )
                ) {
                    Text(
                        text = it,
                        modifier = Modifier.padding(16.dp),
                        color = MaterialTheme.colorScheme.onErrorContainer
                    )
                }
            }

            // Status atual
            Card(
                modifier = Modifier.fillMaxWidth(),
                colors = CardDefaults.cardColors(
                    containerColor = if (isEnabled) 
                        MaterialTheme.colorScheme.primaryContainer 
                    else 
                        MaterialTheme.colorScheme.secondaryContainer
                )
            ) {
                Column(modifier = Modifier.padding(20.dp)) {
                    Text(
                        text = if (isEnabled) "🔒 2FA Ativado" else "🔓 2FA Desativado",
                        style = MaterialTheme.typography.titleLarge,
                        color = if (isEnabled)
                            MaterialTheme.colorScheme.onPrimaryContainer
                        else
                            MaterialTheme.colorScheme.onSecondaryContainer
                    )
                    Spacer(modifier = Modifier.height(8.dp))
                    Text(
                        text = if (isEnabled)
                            "Sua conta está protegida com autenticação em dois fatores."
                        else
                            "Ative o 2FA para maior segurança da sua conta.",
                        style = MaterialTheme.typography.bodyMedium,
                        color = if (isEnabled)
                            MaterialTheme.colorScheme.onPrimaryContainer
                        else
                            MaterialTheme.colorScheme.onSecondaryContainer
                    )
                }
            }

            if (!isEnabled) {
                // Informações sobre 2FA
                Card(modifier = Modifier.fillMaxWidth()) {
                    Column(modifier = Modifier.padding(16.dp)) {
                        Text(
                            text = "Como funciona?",
                            style = MaterialTheme.typography.titleMedium
                        )
                        Spacer(modifier = Modifier.height(8.dp))
                        Text(
                            text = "1. Clique em 'Gerar QR Code'\n" +
                                    "2. Escaneie o QR Code com um app autenticador (Google Authenticator, Authy, etc)\n" +
                                    "3. Digite o código de 6 dígitos gerado pelo app\n" +
                                    "4. Guarde os códigos de backup em local seguro",
                            style = MaterialTheme.typography.bodySmall,
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                    }
                }

                // Botão para gerar QR Code
                if (qrCodeBitmap == null) {
                    Button(
                        onClick = { viewModel.generate2FA() },
                        modifier = Modifier.fillMaxWidth(),
                        enabled = !isLoading
                    ) {
                        if (isLoading) {
                            CircularProgressIndicator(
                                modifier = Modifier.size(20.dp),
                                color = MaterialTheme.colorScheme.onPrimary
                            )
                        } else {
                            Text("Gerar QR Code")
                        }
                    }
                }

                // Mostrar QR Code
                qrCodeBitmap?.let { bitmap ->
                    Card(
                        modifier = Modifier.fillMaxWidth(),
                        colors = CardDefaults.cardColors(
                            containerColor = MaterialTheme.colorScheme.surface
                        )
                    ) {
                        Column(
                            modifier = Modifier
                                .fillMaxWidth()
                                .padding(16.dp),
                            horizontalAlignment = Alignment.CenterHorizontally
                        ) {
                            Text(
                                text = "Escaneie com seu app autenticador:",
                                style = MaterialTheme.typography.titleSmall
                            )
                            Spacer(modifier = Modifier.height(16.dp))
                            Image(
                                bitmap = bitmap.asImageBitmap(),
                                contentDescription = "QR Code 2FA",
                                modifier = Modifier.size(250.dp)
                            )
                            Spacer(modifier = Modifier.height(16.dp))
                            secret?.let {
                                Text(
                                    text = "Chave manual: $it",
                                    style = MaterialTheme.typography.bodySmall,
                                    color = MaterialTheme.colorScheme.onSurfaceVariant
                                )
                            }
                        }
                    }

                    Spacer(modifier = Modifier.height(8.dp))

                    // Input para confirmar
                    OutlinedTextField(
                        value = confirmCode,
                        onValueChange = { if (it.length <= 6) confirmCode = it },
                        label = { Text("Código de 6 dígitos") },
                        modifier = Modifier.fillMaxWidth(),
                        singleLine = true,
                        enabled = !isLoading
                    )

                    Button(
                        onClick = {
                            viewModel.confirm2FA(confirmCode)
                            confirmCode = ""
                        },
                        modifier = Modifier.fillMaxWidth(),
                        enabled = !isLoading && confirmCode.length == 6
                    ) {
                        Text("Confirmar e Ativar 2FA")
                    }
                }
            } else {
                // 2FA já ativado - mostrar códigos de backup se houver
                backupCodes?.let { codes ->
                    if (codes.isNotEmpty()) {
                        Card(
                            modifier = Modifier.fillMaxWidth(),
                            colors = CardDefaults.cardColors(
                                containerColor = MaterialTheme.colorScheme.tertiaryContainer
                            )
                        ) {
                            Column(modifier = Modifier.padding(16.dp)) {
                                Text(
                                    text = "🔑 Códigos de Backup",
                                    style = MaterialTheme.typography.titleMedium,
                                    color = MaterialTheme.colorScheme.onTertiaryContainer
                                )
                                Spacer(modifier = Modifier.height(8.dp))
                                Text(
                                    text = "Guarde estes códigos em local seguro. Você pode usá-los se perder acesso ao app autenticador:",
                                    style = MaterialTheme.typography.bodySmall,
                                    color = MaterialTheme.colorScheme.onTertiaryContainer
                                )
                                Spacer(modifier = Modifier.height(12.dp))
                                codes.forEach { code ->
                                    Text(
                                        text = "• $code",
                                        style = MaterialTheme.typography.bodyMedium,
                                        color = MaterialTheme.colorScheme.onTertiaryContainer,
                                        modifier = Modifier.padding(vertical = 2.dp)
                                    )
                                }
                            }
                        }
                    }
                }

                // Botão para desativar
                OutlinedButton(
                    onClick = { showDisableDialog = true },
                    modifier = Modifier.fillMaxWidth(),
                    colors = ButtonDefaults.outlinedButtonColors(
                        contentColor = MaterialTheme.colorScheme.error
                    )
                ) {
                    Text("Desativar 2FA")
                }
            }

            // Dialog de confirmação para desativar
            if (showDisableDialog) {
                AlertDialog(
                    onDismissRequest = { showDisableDialog = false },
                    title = { Text("Desativar 2FA?") },
                    text = { Text("Tem certeza que deseja desativar a autenticação em dois fatores? Sua conta ficará menos segura.") },
                    confirmButton = {
                        TextButton(
                            onClick = {
                                viewModel.disable2FA()
                                showDisableDialog = false
                            }
                        ) {
                            Text("Desativar", color = MaterialTheme.colorScheme.error)
                        }
                    },
                    dismissButton = {
                        TextButton(onClick = { showDisableDialog = false }) {
                            Text("Cancelar")
                        }
                    }
                )
            }
        }
    }
}
