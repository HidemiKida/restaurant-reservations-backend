<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Restaurant;

class RestaurantOwnerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // SuperAdmin puede acceder a todo
        if ($user->isSuperAdmin()) {
            return $next($request);
        }
        
        // Si es admin, verificar que tenga restaurante asignado
        if ($user->isAdmin()) {
            if (!$user->hasRestaurant()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes un restaurante asignado. Contacta al administrador.'
                ], 403);
            }
            
            // Si hay un ID de restaurante en la ruta, verificar que sea el propietario
            $restaurantId = $request->route('restaurant') ?? $request->route('id');
            
            if ($restaurantId && $user->restaurant->id != $restaurantId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para acceder a este restaurante.'
                ], 403);
            }
        }

        return $next($request);
    }
}