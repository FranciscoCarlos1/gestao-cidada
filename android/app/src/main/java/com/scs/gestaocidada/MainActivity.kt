package com.scs.gestaocidada

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import com.google.android.gms.maps.model.LatLng
import com.scs.gestaocidada.data.TokenManager
import com.scs.gestaocidada.ui.screens.*
import com.scs.gestaocidada.ui.theme.GestaoCidadaTheme

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        
        TokenManager.init(this)
        
        setContent {
            GestaoCidadaTheme {
                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = MaterialTheme.colorScheme.background
                ) {
                    AppNavigation()
                }
            }
        }
    }
}

@Composable
fun AppNavigation() {
    val navController = rememberNavController()
    var userRole by remember { mutableStateOf(TokenManager.getUserRole()) }
    val isLoggedIn = TokenManager.getToken() != null

    val startDestination = if (isLoggedIn) "main" else "login"

    NavHost(
        navController = navController,
        startDestination = startDestination
    ) {
        composable("login") {
            LoginScreen(
                onLoginSuccess = { role ->
                    userRole = role
                    navController.navigate("main") {
                        popUpTo("login") { inclusive = true }
                    }
                }
            )
        }

        composable("main") {
            MainScreen(
                onNavigateToForm = { location ->
                    navController.currentBackStackEntry?.savedStateHandle?.set(
                        "selected_location",
                        location
                    )
                    navController.navigate("form")
                },
                onNavigateToList = {
                    navController.navigate("list")
                },
                onNavigateToAdmin = {
                    navController.navigate("admin")
                },
                onNavigateToAbout = {
                    navController.navigate("about")
                },
                onLogout = {
                    TokenManager.clearToken()
                    TokenManager.clearUserRole()
                    navController.navigate("login") {
                        popUpTo(0) { inclusive = true }
                    }
                },
                userRole = userRole ?: "cidadao"
            )
        }

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

        composable("list") {
            MeusProblemasScreen(
                onNavigateBack = {
                    navController.popBackStack()
                }
            )
        }

        composable("admin") {
            AdminScreen(
                onNavigateBack = {
                    navController.popBackStack()
                }
            )
        }

        composable("about") {
            AboutScreen(
                onNavigateBack = {
                    navController.popBackStack()
                }
            )
        }
    }
}
