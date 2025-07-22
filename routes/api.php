<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected authentication routes
Route::middleware('auth:api')->prefix('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::post('refresh', [AuthController::class, 'refresh']);
});

// Protected API routes will be added here as needed
Route::middleware('auth:api')->group(function () {
    // Test routes for role-based access control
    Route::get('/test/client', function () {
        return response()->json([
            'success' => true,
            'message' => 'Acceso permitido para clientes',
            'user' => auth()->user()->only(['id', 'name', 'email']),
            'roles' => auth()->user()->getRoleNames(),
        ]);
    })->middleware('role:cliente');

    Route::get('/test/admin', function () {
        return response()->json([
            'success' => true,
            'message' => 'Acceso permitido para administradores',
            'user' => auth()->user()->only(['id', 'name', 'email']),
            'roles' => auth()->user()->getRoleNames(),
        ]);
    })->middleware('role:admin,superadmin');

    Route::get('/test/superadmin', function () {
        return response()->json([
            'success' => true,
            'message' => 'Acceso permitido solo para super administradores',
            'user' => auth()->user()->only(['id', 'name', 'email']),
            'roles' => auth()->user()->getRoleNames(),
        ]);
    })->middleware('role:superadmin');
});