<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PrefeituraController;
use App\Http\Controllers\ProblemaController;
use App\Http\Controllers\AdminProblemaController;
use App\Http\Controllers\GeocodeController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CepController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\PrefeituraController as AdminPrefeituraController;
use App\Http\Controllers\Admin\DashboardController;

// ======================
// Auth (Public)
// ======================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/anonimo', [AuthController::class, 'anonimo']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});


// Current user
Route::middleware('auth:sanctum')->get('/me', function (\Illuminate\Http\Request $request) {
    return $request->user()->load(['roles','prefeitura']);
});
// ======================
// 2FA (Auth required)
// ======================
Route::prefix('2fa')->middleware('auth:sanctum')->group(function () {
    Route::post('/generate', [TwoFactorController::class, 'generate']);
    Route::post('/confirm', [TwoFactorController::class, 'confirm']);
    Route::post('/disable', [TwoFactorController::class, 'disable']);
    Route::post('/verify-backup-code', [TwoFactorController::class, 'verifyBackupCode']);
});

// ======================
// Public Data
// ======================
Route::get('/prefeituras', [PrefeituraController::class, 'index']);
Route::get('/problemas', [ProblemaController::class, 'index']); // Público, paginado
Route::get('/geocode/reverse', [GeocodeController::class, 'reverse']);
Route::get('/cep/{cep}', [CepController::class, 'show']);

// ======================
// Anonymous Reporting
// ======================
Route::post('/problemas', [ProblemaController::class, 'store']); // Aceita anônimo ou token

// ======================
// Citizen (Auth required)
// ======================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/problemas/mine', [ProblemaController::class, 'mine']);
    Route::get('/problemas/{problema}', [ProblemaController::class, 'show']);
});

// ======================
// Admin: Users & Roles (Super Admin Only)
// ======================
Route::middleware(['auth:sanctum', 'role:super'])->prefix('admin')->group(function () {
    // Dashboard & Stats
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    Route::get('/activities', [DashboardController::class, 'getRecentActivities']);

    // Users Management
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::post('/users', [AdminUserController::class, 'store']);
    Route::get('/users/{user}', [AdminUserController::class, 'show']);
    Route::put('/users/{user}', [AdminUserController::class, 'update']);
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);
    Route::patch('/users/{user}/status', [AdminUserController::class, 'toggleStatus']);

    // Roles & Permissions
    Route::get('/roles', [AdminRoleController::class, 'index']);
    Route::post('/roles', [AdminRoleController::class, 'store']);
    Route::get('/roles/{role}', [AdminRoleController::class, 'show']);
    Route::put('/roles/{role}', [AdminRoleController::class, 'update']);
    Route::delete('/roles/{role}', [AdminRoleController::class, 'destroy']);
    Route::get('/permissions', [AdminRoleController::class, 'listPermissions']);
    Route::post('/roles/{role}/permissions', [AdminRoleController::class, 'assignPermissions']);
    Route::get('/roles/{role}/users', [AdminRoleController::class, 'getUsers']);

    // Prefeituras
    Route::get('/prefeituras', [AdminPrefeituraController::class, 'index']);
    Route::post('/prefeituras', [AdminPrefeituraController::class, 'store']);
    Route::get('/prefeituras/{prefeitura}', [AdminPrefeituraController::class, 'show']);
    Route::put('/prefeituras/{prefeitura}', [AdminPrefeituraController::class, 'update']);
    Route::delete('/prefeituras/{prefeitura}', [AdminPrefeituraController::class, 'destroy']);

    // Reports
    Route::get('/reports/problemas', [DashboardController::class, 'getProblemsReport']);
    Route::get('/reports/problemas/export', [DashboardController::class, 'exportProblemsReport']);
    Route::get('/reports/users', [DashboardController::class, 'getUsersReport']);
    Route::get('/reports/users/export', [DashboardController::class, 'exportUsersReport']);
    Route::get('/audit-logs', [DashboardController::class, 'getAuditLogs']);
    Route::get('/audit-logs/export', [DashboardController::class, 'exportAuditLogs']);
});

// ======================
// Firebase Cloud Messaging (FCM)
// ======================
Route::middleware('auth:sanctum')->group(function () {
    // Salvar/remover token FCM do usuário
    Route::post('/fcm/token', [NotificationController::class, 'saveFcmToken']);
    Route::delete('/fcm/token', [NotificationController::class, 'removeFcmToken']);

    // Notificação de teste
    Route::post('/notifications/test', [NotificationController::class, 'sendTestNotification']);

    // Enviar notificação sobre um problema específico (admin/prefeitura)
    Route::post('/problemas/{id}/notify', [NotificationController::class, 'notifyProblemaUpdate'])
        ->middleware('role:super,admin,prefeitura');

    // Broadcast para todos os cidadãos (admin/prefeitura)
    Route::post('/notifications/broadcast', [NotificationController::class, 'broadcastNotification'])
        ->middleware('role:super,admin,prefeitura');
});

