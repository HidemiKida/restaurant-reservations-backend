<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    // Constantes para los roles
    const ROLE_CLIENTE = 'cliente';
    const ROLE_ADMIN = 'admin';
    const ROLE_SUPERADMIN = 'superadmin';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function restaurant()                    // ← NUEVO
    {
        return $this->hasOne(Restaurant::class, 'owner_id');
    }

    public function reservations()                  // ← NUEVO
    {
        return $this->hasMany(Reservation::class);
    }

    // JWT Methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Helper Methods
    public function isAdmin()                       // ← NUEVO
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isSuperAdmin()                  // ← NUEVO
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function isCliente()                     // ← NUEVO
    {
        return $this->role === self::ROLE_CLIENTE;
    }

    public function hasRestaurant()                 // ← NUEVO
    {
        return $this->restaurant()->exists();
    }
}
