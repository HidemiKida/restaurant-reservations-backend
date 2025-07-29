<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ReservationController extends Controller
{
    /**
     * Listar las reservaciones del cliente autenticado
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = $user->reservations()
            ->with(['restaurant', 'table'])
            ->orderBy('reservation_date', 'desc');
        
        // Filtrar por estado si se proporciona
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtrar por fecha si se proporciona
        if ($request->has('date')) {
            $query->whereDate('reservation_date', $request->date);
        }
        
        $reservations = $query->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $reservations
        ]);
    }
    
    /**
     * Crear nueva reservación
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required|exists:restaurants,id',
            'table_id' => 'required|exists:tables,id',
            'reservation_date' => 'required|date|after:now',
            'party_size' => 'required|integer|min:1|max:20',
            'special_requests' => 'nullable|string|max:500',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verificar que el restaurante esté activo
        $restaurant = Restaurant::active()->find($request->restaurant_id);
        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurante no disponible'
            ], 404);
        }
        
        // Verificar que la mesa pertenece al restaurante
        $table = Table::where('id', $request->table_id)
            ->where('restaurant_id', $request->restaurant_id)
            ->available()
            ->first();
            
        if (!$table) {
            return response()->json([
                'success' => false,
                'message' => 'Mesa no disponible'
            ], 404);
        }
        
        // Verificar capacidad de la mesa
        if ($request->party_size > $table->capacity) {
            return response()->json([
                'success' => false,
                'message' => "La mesa seleccionada tiene capacidad para {$table->capacity} personas máximo"
            ], 422);
        }
        
        // Verificar disponibilidad de la mesa en esa fecha/hora
        $reservationDateTime = Carbon::parse($request->reservation_date);
        
        // Verificar horarios del restaurante
        $openingTime = Carbon::parse($restaurant->opening_time)->format('H:i');
        $closingTime = Carbon::parse($restaurant->closing_time)->format('H:i');
        $requestTime = $reservationDateTime->format('H:i');
        
        if ($requestTime < $openingTime || $requestTime > $closingTime) {
            return response()->json([
                'success' => false,
                'message' => "El restaurante está cerrado a esa hora. Horario: {$openingTime} - {$closingTime}"
            ], 422);
        }
        
        // Verificar días de apertura
        $dayOfWeek = strtolower($reservationDateTime->format('l'));
        if (!in_array($dayOfWeek, $restaurant->opening_days)) {
            return response()->json([
                'success' => false,
                'message' => 'El restaurante está cerrado ese día'
            ], 422);
        }
        
        // Verificar conflictos de reservación (misma mesa, mismo día, ±2 horas)
        $conflictStart = $reservationDateTime->copy()->subHours(2);
        $conflictEnd = $reservationDateTime->copy()->addHours(2);
        
        $existingReservation = Reservation::where('table_id', $request->table_id)
            ->where('status', '!=', 'cancelada')
            ->whereBetween('reservation_date', [$conflictStart, $conflictEnd])
            ->exists();
            
        if ($existingReservation) {
            return response()->json([
                'success' => false,
                'message' => 'La mesa no está disponible en ese horario'
            ], 422);
        }
        
        // Crear la reservación
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'restaurant_id' => $request->restaurant_id,
            'table_id' => $request->table_id,
            'reservation_date' => $request->reservation_date,
            'party_size' => $request->party_size,
            'special_requests' => $request->special_requests,
            'status' => 'pendiente',
        ]);
        
        // Cargar relaciones para la respuesta
        $reservation->load(['restaurant', 'table']);
        
        return response()->json([
            'success' => true,
            'message' => 'Reservación creada exitosamente',
            'data' => $reservation
        ], 201);
    }
    
    /**
     * Mostrar reservación específica
     */
    public function show($id)
    {
        $user = auth()->user();
        
        $reservation = $user->reservations()
            ->with(['restaurant', 'table'])
            ->find($id);
            
        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservación no encontrada'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $reservation
        ]);
    }
    
    /**
     * Cancelar reservación
     */
    public function cancel($id)
    {
        $user = auth()->user();
        
        $reservation = $user->reservations()->find($id);
        
        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservación no encontrada'
            ], 404);
        }
        
        // Verificar que la reservación se pueda cancelar
        if ($reservation->status === 'cancelada') {
            return response()->json([
                'success' => false,
                'message' => 'La reservación ya está cancelada'
            ], 422);
        }
        
        if ($reservation->status === 'completada') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede cancelar una reservación completada'
            ], 422);
        }
        
        // Verificar que no sea muy tarde para cancelar (ej: 2 horas antes)
        $reservationTime = Carbon::parse($reservation->reservation_date);
        $minCancelTime = $reservationTime->copy()->subHours(2);
        
        if (now() > $minCancelTime) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede cancelar con menos de 2 horas de anticipación'
            ], 422);
        }
        
        $reservation->update([
            'status' => 'cancelada',
            'cancelled_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Reservación cancelada exitosamente',
            'data' => $reservation->fresh()
        ]);
    }
    
    /**
     * Obtener mesas disponibles para un restaurante en una fecha/hora específica
     */
    public function availableTables(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required|exists:restaurants,id',
            'reservation_date' => 'required|date|after:now',
            'party_size' => 'required|integer|min:1|max:20',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $restaurant = Restaurant::active()->find($request->restaurant_id);
        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurante no disponible'
            ], 404);
        }
        
        $reservationDateTime = Carbon::parse($request->reservation_date);
        
        // Verificar horarios y días de apertura (mismo código que arriba)
        $openingTime = Carbon::parse($restaurant->opening_time)->format('H:i');
        $closingTime = Carbon::parse($restaurant->closing_time)->format('H:i');
        $requestTime = $reservationDateTime->format('H:i');
        
        if ($requestTime < $openingTime || $requestTime > $closingTime) {
            return response()->json([
                'success' => false,
                'message' => "El restaurante está cerrado a esa hora. Horario: {$openingTime} - {$closingTime}"
            ], 422);
        }
        
        $dayOfWeek = strtolower($reservationDateTime->format('l'));
        if (!in_array($dayOfWeek, $restaurant->opening_days)) {
            return response()->json([
                'success' => false,
                'message' => 'El restaurante está cerrado ese día'
            ], 422);
        }
        
        // Obtener mesas disponibles
        $conflictStart = $reservationDateTime->copy()->subHours(2);
        $conflictEnd = $reservationDateTime->copy()->addHours(2);
        
        $unavailableTableIds = Reservation::where('restaurant_id', $request->restaurant_id)
            ->where('status', '!=', 'cancelada')
            ->whereBetween('reservation_date', [$conflictStart, $conflictEnd])
            ->pluck('table_id');
        
        $availableTables = $restaurant->tables()
            ->available()
            ->where('capacity', '>=', $request->party_size)
            ->whereNotIn('id', $unavailableTableIds)
            ->orderBy('capacity')
            ->orderBy('table_number')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $availableTables
        ]);
    }
}