<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TableController extends Controller
{
    /**
     * Listar todas las mesas del restaurante del admin
     */
    public function index()
    {
        $user = auth()->user();
        
        if (!$user->hasRestaurant()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes un restaurante asignado'
            ], 404);
        }
        
        $tables = $user->restaurant->tables()
            ->with(['reservations' => function($query) {
                $query->whereDate('reservation_date', '>=', today())
                      ->whereIn('status', ['pendiente', 'confirmada']);
            }])
            ->orderBy('table_number')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $tables
        ]);
    }
    
    /**
     * Crear nueva mesa
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->hasRestaurant()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes un restaurante asignado'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'table_number' => 'required|string|max:10',
            'capacity' => 'required|integer|min:1|max:20',
            'location' => 'required|in:interior,exterior,privado,bar',
            'notes' => 'nullable|string|max:500',
            'is_available' => 'sometimes|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verificar que el número de mesa no esté duplicado en el restaurante
        $existingTable = $user->restaurant->tables()
            ->where('table_number', $request->table_number)
            ->exists();
            
        if ($existingTable) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una mesa con ese número en tu restaurante'
            ], 422);
        }
        
        $table = $user->restaurant->tables()->create($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Mesa creada exitosamente',
            'data' => $table
        ], 201);
    }
    
    /**
     * Mostrar mesa específica
     */
    public function show($id)
    {
        $user = auth()->user();
        
        if (!$user->hasRestaurant()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes un restaurante asignado'
            ], 404);
        }
        
        $table = $user->restaurant->tables()
            ->with(['reservations' => function($query) {
                $query->whereDate('reservation_date', '>=', today())
                      ->whereIn('status', ['pendiente', 'confirmada'])
                      ->orderBy('reservation_date');
            }])
            ->find($id);
            
        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'Mesa no encontrada'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $table
        ]);
    }
    
    /**
     * Actualizar mesa
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        
        if (!$user->hasRestaurant()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes un restaurante asignado'
            ], 404);
        }
        
        $table = $user->restaurant->tables()->find($id);
        
        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'Mesa no encontrada'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'table_number' => 'sometimes|string|max:10',
            'capacity' => 'sometimes|integer|min:1|max:20',
            'location' => 'sometimes|in:interior,exterior,privado,bar',
            'notes' => 'nullable|string|max:500',
            'is_available' => 'sometimes|boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verificar duplicado de número de mesa si se está cambiando
        if ($request->has('table_number') && $request->table_number !== $table->table_number) {
            $existingTable = $user->restaurant->tables()
                ->where('table_number', $request->table_number)
                ->where('id', '!=', $id)
                ->exists();
                
            if ($existingTable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una mesa con ese número en tu restaurante'
                ], 422);
            }
        }
        
        $table->update($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Mesa actualizada exitosamente',
            'data' => $table->fresh()
        ]);
    }
    
    /**
     * Eliminar mesa
     */
    public function destroy($id)
    {
        $user = auth()->user();
        
        if (!$user->hasRestaurant()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes un restaurante asignado'
            ], 404);
        }
        
        $table = $user->restaurant->tables()->find($id);
        
        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'Mesa no encontrada'
            ], 404);
        }
        
        // Verificar si hay reservaciones activas
        $activeReservations = $table->reservations()
            ->whereDate('reservation_date', '>=', today())
            ->whereIn('status', ['pendiente', 'confirmada'])
            ->exists();
            
        if ($activeReservations) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la mesa porque tiene reservaciones activas'
            ], 422);
        }
        
        $table->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Mesa eliminada exitosamente'
        ]);
    }
}