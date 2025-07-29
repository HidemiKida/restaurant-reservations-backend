<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RestaurantController;
use App\Http\Controllers\API\Admin\RestaurantController as AdminRestaurantController;
use App\Http\Controllers\API\Admin\TableController as AdminTableController;
use App\Http\Controllers\API\Client\ReservationController;
use App\Http\Controllers\API\Client\ServiceController;

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
    
    // Rutas públicas para clientes (ver restaurantes)
    Route::prefix('restaurants')->group(function () {
        Route::get('/', [RestaurantController::class, 'index']); // Listar restaurantes
        Route::get('/{id}', [RestaurantController::class, 'show']); // Detalle de restaurante
    });
    
    // Rutas para clientes
    Route::middleware('client')->prefix('client')->group(function () {
        
        // Gestión de reservaciones
        Route::prefix('reservations')->group(function () {
            Route::get('/', [ReservationController::class, 'index']);
            Route::post('/', [ReservationController::class, 'store']);
            Route::get('/{id}', [ReservationController::class, 'show']);
            Route::patch('/{id}/cancel', [ReservationController::class, 'cancel']);
        });
        
        // Mesas disponibles
        Route::get('/available-tables', [ReservationController::class, 'availableTables']);
        
        // Servicio de restaurante
        Route::prefix('service')->group(function () {
            Route::get('/check-eligibility', [ServiceController::class, 'checkEligibility']);
            Route::post('/purchase-restaurant', [ServiceController::class, 'purchaseRestaurantService']);
        });
        
    });
    
    // Rutas para administradores de restaurantes
    Route::middleware(['admin', 'restaurant.owner'])->prefix('admin')->group(function () {
        
        // Gestión del restaurante propio
        Route::prefix('restaurant')->group(function () {
            Route::get('/', [AdminRestaurantController::class, 'show']);
            Route::put('/', [AdminRestaurantController::class, 'update']);
            Route::get('/stats', [AdminRestaurantController::class, 'stats']);
        });
        
        // Gestión de mesas
        Route::prefix('tables')->group(function () {
            Route::get('/', [AdminTableController::class, 'index']);
            Route::post('/', [AdminTableController::class, 'store']);
            Route::get('/{id}', [AdminTableController::class, 'show']);
            Route::put('/{id}', [AdminTableController::class, 'update']);
            Route::delete('/{id}', [AdminTableController::class, 'destroy']);
        });
        
    });
    
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