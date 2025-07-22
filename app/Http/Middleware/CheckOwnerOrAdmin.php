<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Role;

class CheckOwnerOrAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $paramName  The route parameter name to check ownership (default: 'id')
     */
    public function handle(Request $request, Closure $next, ?string $paramName = 'id'): Response
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        // If user is admin or superadmin, allow access
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check if user is owner of the resource
        $resourceId = $request->route($paramName);
        
        if (!$resourceId) {
            return response()->json([
                'success' => false,
                'message' => 'ID de recurso no encontrado'
            ], 400);
        }

        // For user resources, check if the authenticated user ID matches the requested ID
        if ($paramName === 'id' && $user->id == $resourceId) {
            return $next($request);
        }

        // For other resources, you might need to implement specific ownership logic
        // This is a basic implementation that can be extended based on your needs

        return response()->json([
            'success' => false,
            'message' => 'No tienes permisos para acceder a este recurso'
        ], 403);
    }
}