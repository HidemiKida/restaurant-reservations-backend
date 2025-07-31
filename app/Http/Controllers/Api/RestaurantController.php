<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    /**
     * Listar todos los restaurantes activos
     */
    public function index(Request $request)
    {
        $query = Restaurant::active()
            ->with(['tables' => function($query) {
                $query->available();
            }]);
        
        // Filtro por tipo de cocina
        if ($request->has('cuisine_type')) {
            $query->where('cuisine_type', 'like', '%' . $request->cuisine_type . '%');
        }
        
        // Filtro de búsqueda por nombre o descripción
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        // Filtro por ciudad (extraído del address)
        if ($request->has('city')) {
            $query->where('address', 'like', '%' . $request->city . '%');
        }
        
        // Ordenar por nombre por defecto
        $query->orderBy('name', 'asc');
        
        // Paginación
        $restaurants = $query->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $restaurants,
            'message' => 'Restaurantes obtenidos exitosamente'
        ]);
    }
    
    /**
     * Mostrar detalles de un restaurante específico
     */
    public function show($id)
    {
        $restaurant = Restaurant::active()
            ->with(['tables' => function($query) {
                $query->available()->orderBy('table_number');
            }])
            ->find($id);
        
        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurante no encontrado o no disponible'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $restaurant,
            'message' => 'Detalles del restaurante obtenidos exitosamente'
        ]);
    }
}