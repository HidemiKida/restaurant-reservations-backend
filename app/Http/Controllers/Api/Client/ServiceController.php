<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    /**
     * Comprar servicio para obtener restaurante propio
     */
    public function purchaseRestaurantService(Request $request)
    {
        $user = auth()->user();
        
        // Verificar que el usuario sea cliente
        if (!$user->isCliente()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo los clientes pueden comprar este servicio'
            ], 403);
        }
        
        // Verificar que el usuario no tenga ya un restaurante
        if ($user->hasRestaurant()) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes un restaurante asignado'
            ], 422);
        }
        
        $validator = Validator::make($request->all(), [
            'restaurant_name' => 'required|string|max:255',
            'restaurant_description' => 'nullable|string|max:1000',
            'restaurant_address' => 'required|string|max:255',
            'restaurant_phone' => 'required|string|max:20',
            'restaurant_email' => 'required|email|max:255',
            'cuisine_type' => 'required|string|max:255',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i|after:opening_time',
            'opening_days' => 'required|array|min:1',
            'opening_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'max_capacity' => 'required|integer|min:10|max:500',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            // 1. Crear el restaurante
            $restaurant = Restaurant::create([
                'owner_id' => $user->id,
                'name' => $request->restaurant_name,
                'description' => $request->restaurant_description,
                'address' => $request->restaurant_address,
                'phone' => $request->restaurant_phone,
                'email' => $request->restaurant_email,
                'cuisine_type' => $request->cuisine_type,
                'opening_time' => $request->opening_time,
                'closing_time' => $request->closing_time,
                'opening_days' => $request->opening_days,
                'max_capacity' => $request->max_capacity,
                'is_active' => true,
            ]);
            
            // 2. Cambiar el rol del usuario a admin
            $user->update([
                'role' => User::ROLE_ADMIN
            ]);
            
            // 3. Crear algunas mesas básicas por defecto
            $defaultTables = [
                ['table_number' => '1', 'capacity' => 2, 'location' => 'interior'],
                ['table_number' => '2', 'capacity' => 4, 'location' => 'interior'],
                ['table_number' => '3', 'capacity' => 4, 'location' => 'interior'],
                ['table_number' => '4', 'capacity' => 6, 'location' => 'interior'],
                ['table_number' => '5', 'capacity' => 8, 'location' => 'interior'],
            ];
            
            foreach ($defaultTables as $tableData) {
                $restaurant->tables()->create($tableData);
            }
            
            DB::commit();
            
            // Regenerar token JWT con el nuevo rol
            $newToken = auth()->refresh();
            
            return response()->json([
                'success' => true,
                'message' => '¡Felicidades! Has adquirido tu servicio de restaurante exitosamente',
                'data' => [
                    'user' => $user->fresh(),
                    'restaurant' => $restaurant->load('tables'),
                    'new_token' => $newToken
                ]
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la compra del servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Verificar si el usuario puede comprar el servicio
     */
    public function checkEligibility()
    {
        $user = auth()->user();
        
        $canPurchase = $user->isCliente() && !$user->hasRestaurant();
        
        return response()->json([
            'success' => true,
            'data' => [
                'can_purchase' => $canPurchase,
                'current_role' => $user->role,
                'has_restaurant' => $user->hasRestaurant(),
                'message' => $canPurchase 
                    ? 'Puedes adquirir el servicio de restaurante'
                    : 'No puedes adquirir el servicio en este momento'
            ]
        ]);
    }
}