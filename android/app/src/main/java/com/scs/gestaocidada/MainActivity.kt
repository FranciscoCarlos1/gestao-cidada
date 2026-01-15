package com.scs.gestaocidada

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.animation.*
import androidx.compose.animation.core.tween
import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import com.google.accompanist.systemuicontroller.rememberSystemUiController
import com.google.android.gms.maps.model.LatLng
import com.scs.gestaocidada.data.PreferencesManager
import com.scs.gestaocidada.data.TokenManager
import com.scs.gestaocidada.ui.screens.*
import com.scs.gestaocidada.ui.theme.GestaoCidadaTheme

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        
        TokenManager.init(this)
        val preferencesManager = PreferencesManager.getInstance(this)
        
        setContent {
            val darkMode by preferencesManager.darkModeFlow.collectAsState(initial = isSystemInDarkTheme())
            val systemUiController = rememberSystemUiController()
            
            GestaoCidadaTheme(darkTheme = darkMode) {
                // Configurar cor da status bar
                SideEffect {
                    systemUiController.setSystemBarsColor(
                        color = if (darkMode) 
                            androidx.compose.ui.graphics.Color(0xFF121212) 
                        else 
                            androidx.compose.ui.graphics.Color(0xFFF5F5F5),
                        darkIcons = !darkMode
                    )
                }
                
                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = MaterialTheme.colorScheme.background
                ) {
                    AppNavigation(preferencesManager)
                }
            }
        }
    }
}

@Composable
fun AppNavigation(preferencesManager: PreferencesManager) {
    val navController = rememberNavController()
    var userRole by remember { mutableStateOf(TokenManager.getUserRole()) }
    val isLoggedIn = TokenManager.getToken() != null

    // Rota inicial baseada em login
    val startDestination = if (isLoggedIn) {
        when (userRole) {
            "super" -> "super_admin"
            "admin" -> "admin"
            "cidadao" -> "cidadao"
            else -> "anonimo"
        }
    } else "anonimo"

    NavHost(
        navController = navController,
        startDestination = startDestination,
        enterTransition = {
            fadeIn(animationSpec = tween(300)) + slideInHorizontally(
                initialOffsetX = { 300 },
                animationSpec = tween(300)
            )
        },
        exitTransition = {
            fadeOut(animationSpec = tween(300))
        },
        popEnterTransition = {
            fadeIn(animationSpec = tween(300))
        },
        popExitTransition = {
            fadeOut(animationSpec = tween(300)) + slideOutHorizontally(
                targetOffsetX = { 300 },
                animationSpec = tween(300)
            )
        }
    ) {
        // Tela Anônimo (visitante)
        composable("anonimo") {
            AnonimoScreen(
                onNavigateToLogin = {
                    navController.navigate("login") {
                        popUpTo("anonimo") { inclusive = true }
                    }
                }
            )
        }

        // Tela de Login
        composable("login") {
            LoginScreen(
                onLoginSuccess = { role ->
                    userRole = role
                    val destination = when (role) {
                        "super" -> "super_admin"
                        "admin" -> "admin"
                        "cidadao" -> "cidadao"
                        else -> "anonimo"
                    }
                    navController.navigate(destination) {
                        popUpTo("login") { inclusive = true }
                    }
                }
            )
        }

        // Dashboard Cidadão
        composable("cidadao") {
            CidadaoScreen(
                preferencesManager = preferencesManager,
                onNavigateToMap = {
                    navController.navigate("map")
                },
                onNavigateToForm = { location ->
                    navController.currentBackStackEntry?.savedStateHandle?.set(
                        "selected_location",
                        location
                    )
                    navController.navigate("form")
                },
                onNavigateToMyProblemas = {
                    navController.navigate("my_problemas")
                },
                onNavigateTo2FA = {
                    navController.navigate("two_factor")
                },
                onNavigateToSettings = {
                    navController.navigate("settings")
                },
                onNavigateToAbout = {
                    navController.navigate("about")
                },
                onLogout = {
                    TokenManager.clearAll()
                    navController.navigate("anonimo") {
                        popUpTo(0) { inclusive = true }
                    }
                }
            )
        }

        // Tela do Mapa (para escolher localização)
        composable("map") {
            MainScreen(
                onNavigateToForm = { location ->
                    navController.currentBackStackEntry?.savedStateHandle?.set(
                        "selected_location",
                        location
                    )
                    navController.navigate("form")
                },
                onNavigateToList = {
                    navController.navigate("my_problemas")
                },
                onNavigateToAdmin = {
                    if (userRole == "admin") navController.navigate("admin")
                    else if (userRole == "super") navController.navigate("super_admin")
                },
                onNavigateToAbout = {
                    navController.navigate("about")
                },
                onLogout = {
                    TokenManager.clearAll()
                    navController.navigate("anonimo") {
                        popUpTo(0) { inclusive = true }
                    }
                },
                userRole = userRole ?: "cidadao"
            )
        }

        // Formulário de Problema
        composable("form") {
            val location = navController.previousBackStackEntry
                ?.savedStateHandle
                ?.get<LatLng?>("selected_location")
            
            ProblemaFormScreen(
                selectedLocation = location,
                onNavigateBack = {
                    navController.popBackStack()
                }
            )
        }

        // Meus Problemas (Cidadão)
        composable("my_problemas") {
            MeusProblemasScreen(
                onNavigateBack = {
                    navController.popBackStack()
                }
            )
        }

        // 2FA (Cidadão)
        composable("two_factor") {
            TwoFactorScreen(
                onNavigateBack = {
                    navController.popBackStack()
                }
            )
        }

        // 2FA (Cidadão)
        composable("two_factor") {
            TwoFactorScreen(
                onNavigateBack = {
                    navController.popBackStack()
                }
            )
        }

        // Admin Prefeitura
        composable("admin") {
            AdminScreen(
                onNavigateBack = {
                    navController.popBackStack()
                }
            )
        }

        // Super Admin
        composable("super_admin") {
            SuperAdminScreen(
                onNavigateBack = {
                    navController.popBackStack()
                }
            )
        }

        // Sobre
        composable("about") {
            AboutScreen(
                onNavigateBack = {
                    navController.popBackStack()
                }
            )
        }
        
        // Configurações
        composable("settings") {
            SettingsScreen(
                preferencesManager = preferencesManager,
                onNavigateBack = {
                    navController.popBackStack()
                }
            )
        }
    }
}
