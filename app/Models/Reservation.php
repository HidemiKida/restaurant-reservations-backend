<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'restaurant_id',
        'table_id',
        'reservation_date',
        'party_size',
        'status',
        'special_requests',
        'notes',
        'confirmed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'reservation_date' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pendiente');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmada');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('reservation_date', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('reservation_date', '>=', now());
    }

    // Methods
    public function confirm()
    {
        $this->update([
            'status' => 'confirmada',
            'confirmed_at' => now(),
        ]);
    }

    public function cancel()
    {
        $this->update([
            'status' => 'cancelada',
            'cancelled_at' => now(),
        ]);
    }

    public function complete()
    {
        $this->update(['status' => 'completada']);
    }

    public function isPending()
    {
        return $this->status === 'pendiente';
    }

    public function isConfirmed()
    {
        return $this->status === 'confirmada';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelada';
    }
}