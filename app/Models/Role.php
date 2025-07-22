<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the users that have this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    /**
     * Get role by name.
     */
    public static function findByName(string $name): ?Role
    {
        return static::where('name', $name)->first();
    }

    /**
     * Role constants
     */
    public const CLIENT = 'cliente';
    public const ADMIN = 'admin';
    public const SUPERADMIN = 'superadmin';

    /**
     * Get all available roles
     */
    public static function getAllRoles(): array
    {
        return [
            self::CLIENT,
            self::ADMIN,
            self::SUPERADMIN,
        ];
    }
}