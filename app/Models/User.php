<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * Get the roles assigned to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user has any of the specified roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Check if user is an admin or superadmin.
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole([Role::ADMIN, Role::SUPERADMIN]);
    }

    /**
     * Check if user is a superadmin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(Role::SUPERADMIN);
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(string $roleName): void
    {
        $role = Role::findByName($roleName);
        if ($role && !$this->hasRole($roleName)) {
            $this->roles()->attach($role);
        }
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(string $roleName): void
    {
        $role = Role::findByName($roleName);
        if ($role) {
            $this->roles()->detach($role);
        }
    }

    /**
     * Get user role names as array.
     */
    public function getRoleNames(): array
    {
        return $this->roles->pluck('name')->toArray();
    }
}
