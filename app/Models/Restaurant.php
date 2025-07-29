<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restaurant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'address',
        'phone',
        'email',
        'opening_time',
        'closing_time',
        'opening_days',
        'max_capacity',
        'cuisine_type',
        'image_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'opening_days' => 'array',
            'opening_time' => 'datetime:H:i',
            'closing_time' => 'datetime:H:i',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Methods
    public function isOpenOn($day)
    {
        return in_array(strtolower($day), $this->opening_days);
    }

    public function getAvailableTablesForDateTime($dateTime, $partySize)
    {
        return $this->tables()
            ->where('capacity', '>=', $partySize)
            ->where('is_available', true)
            ->whereDoesntHave('reservations', function ($query) use ($dateTime) {
                $query->where('reservation_date', $dateTime)
                      ->whereIn('status', ['pendiente', 'confirmada']);
            })
            ->get();
    }
}