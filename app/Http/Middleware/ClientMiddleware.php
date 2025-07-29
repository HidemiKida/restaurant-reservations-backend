<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado'
            ], 401);
        }

        $user = auth()->user();
        
        if (!$user->isCliente() && !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado. Solo clientes pueden acceder a esta funcionalidad.'
            ], 403);
        }

        return $next($request);
    }
}