package com.scs.gestaocidada.ui

import androidx.compose.runtime.Composable
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import com.scs.gestaocidada.data.Session

@Composable
fun AppNav() {
    val nav = rememberNavController()
    val start = if (Session.token != null) "home" else "login"

    NavHost(navController = nav, startDestination = start) {
        composable("login") {
            LoginScreen(onDone = { nav.navigate("home") { popUpTo("login") { inclusive = true } } })
        }
        composable("home") {
            HomeScreen(
                onNovo = { nav.navigate("novo") },
                onLogout = { nav.navigate("login") { popUpTo("home") { inclusive = true } } }
            )
        }
        composable("novo") {
            NovoProblemaScreen(onDone = { nav.popBackStack() })
        }
    }
}
