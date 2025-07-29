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

    /**
     * Boot method para establecer valores por defecto
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($user) {
            if (empty($user->role)) {
                $user->role = self::ROLE_CLIENTE;
            }
        });
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

    // Helper methods para roles
    public function isCliente()
    {
        return $this->role === self::ROLE_CLIENTE;
    }

    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isSuperAdmin()
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function isAdminOrSuperAdmin()
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPERADMIN]);
    }

    /**
     * Obtener avatar basado en rol
     */
    public function getAvatarAttribute()
    {
        return match($this->role) {
            self::ROLE_SUPERADMIN => '👑',
            self::ROLE_ADMIN => '👨‍💼',
            self::ROLE_CLIENTE => '🥢',
            default => '👤'
        };
    }

    /**
     * Obtener nombre del rol en formato amigable
     */
    public function getRoleNameAttribute()
    {
        return match($this->role) {
            self::ROLE_SUPERADMIN => 'Super Administrador',
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_CLIENTE => 'Cliente',
            default => 'Usuario'
        };
    }

    /**
     * Obtener todos los roles disponibles
     */
    public static function getRoles()
    {
        return [
            self::ROLE_CLIENTE => 'Cliente',
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_SUPERADMIN => 'Super Administrador',
        ];
    }
}