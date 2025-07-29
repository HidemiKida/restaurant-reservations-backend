<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RestaurantController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas (sin autenticación)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Rutas protegidas (requieren autenticación)
Route::middleware('auth:api')->group(function () {
    
    // Rutas de autenticación
    Route::prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
    
Route::prefix('restaurants')->group(function () {
    Route::get('/', [RestaurantController::class, 'index']); // Listar restaurantes
    Route::get('/{id}', [RestaurantController::class, 'show']); // Detalle de restaurante
});

    // Otras rutas protegidas pueden ir aquí
});

// Ruta de prueba
Route::get('test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API funcionando correctamente! 🚀',
        'timestamp' => now()->toISOString(),
        'laravel_version' => app()->version()
    ]);
});