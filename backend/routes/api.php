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
// Admin: Users & Roles
// ======================
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    // Users (super admin only)
    Route::middleware('role:super')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::patch('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
    });

    // Roles & Permissions (super admin only)
    Route::middleware('role:super')->group(function () {
        Route::get('/roles', [RoleController::class, 'index']);
        Route::post('/roles', [RoleController::class, 'store']);
        Route::get('/roles/{role}', [RoleController::class, 'show']);
        Route::patch('/roles/{role}', [RoleController::class, 'update']);
        Route::delete('/roles/{role}', [RoleController::class, 'destroy']);
        Route::get('/permissions', [RoleController::class, 'permissions']);
        Route::post('/roles/{role}/permissions/{permission}', [RoleController::class, 'grantPermission']);
        Route::delete('/roles/{role}/permissions/{permission}', [RoleController::class, 'revokePermission']);
    });

    // Prefeituras (super admin only)
    Route::middleware('role:super')->group(function () {
        Route::post('/prefeituras', [PrefeituraController::class, 'store']);
        Route::get('/prefeituras/{prefeitura}', [PrefeituraController::class, 'show']);
        Route::patch('/prefeituras/{prefeitura}', [PrefeituraController::class, 'update']);
        Route::delete('/prefeituras/{prefeitura}', [PrefeituraController::class, 'destroy']);
        Route::get('/prefeituras/{prefeitura}/webhooks', [PrefeituraController::class, 'webhooks']);
        Route::post('/prefeituras/{prefeitura}/webhooks', [PrefeituraController::class, 'createWebhook']);
        Route::delete('/prefeituras/{prefeitura}/webhooks/{webhookId}', [PrefeituraController::class, 'deleteWebhook']);
    });

    // Audit Logs (super admin only)
    Route::middleware('role:super')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show']);
    });

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

