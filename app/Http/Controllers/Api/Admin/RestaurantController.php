<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class RestaurantController extends Controller
{
    /**
     * Obtener el restaurante del admin autenticado
     */
    public function show()
    {
        $user = auth()->user();
        
        if ($user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Los superadministradores no tienen restaurante asignado'
            ], 400);
        }
        
        if (!$user->hasRestaurant()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes un restaurante asignado'
            ], 404);
        }
        
        $restaurant = $user->restaurant()->with(['tables'])->first();
        
        return response()->json([
            'success' => true,
            'data' => $restaurant
        ]);
    }
    
    /**
     * Actualizar información del restaurante
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->hasRestaurant()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes un restaurante asignado'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'address' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|max:255',
            'opening_time' => 'sometimes|date_format:H:i',
            'closing_time' => 'sometimes|date_format:H:i',
            'opening_days' => 'sometimes|array',
            'opening_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'max_capacity' => 'sometimes|integer|min:1',
            'cuisine_type' => 'sometimes|string|max:255',
            'image' => 'sometimes|image|max:2048', // máximo 2MB
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $restaurant = $user->restaurant;
        $data = $request->except(['image']);
        
        // Manejar subida de imagen
        if ($request->hasFile('image')) {
            // Eliminar imagen anterior si existe
            if ($restaurant->image_url) {
                Storage::disk('public')->delete($restaurant->image_url);
            }
            
            $imagePath = $request->file('image')->store('restaurants', 'public');
            $data['image_url'] = $imagePath;
        }
        
        $restaurant->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Restaurante actualizado exitosamente',
            'data' => $restaurant->fresh()
        ]);
    }
    
    /**
     * Obtener estadísticas del restaurante
     */
    public function stats()
    {
        $user = auth()->user();
        
        if (!$user->hasRestaurant()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes un restaurante asignado'
            ], 404);
        }
        
        $restaurant = $user->restaurant;
        
        $stats = [
            'total_tables' => $restaurant->tables()->count(),
            'active_tables' => $restaurant->tables()->available()->count(),
            'total_reservations' => $restaurant->reservations()->count(),
            'pending_reservations' => $restaurant->reservations()->pending()->count(),
            'confirmed_reservations' => $restaurant->reservations()->where('status', 'confirmada')->count(),
            'today_reservations' => $restaurant->reservations()
                ->whereDate('reservation_date', today())
                ->count(),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}