<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear superadministrador
        User::updateOrCreate(
            ['email' => 'superadmin@restaurant.com'],
            [
                'name' => 'Super Administrador',
                'email' => 'superadmin@restaurant.com',
                'password' => Hash::make('super123'),
                'role' => User::ROLE_SUPERADMIN,
                'phone' => '+1111111111',
                'email_verified_at' => now(),
            ]
        );

        // Crear administrador
        User::updateOrCreate(
            ['email' => 'admin@restaurant.com'],
            [
                'name' => 'Administrador',
                'email' => 'admin@restaurant.com',
                'password' => Hash::make('admin123'),
                'role' => User::ROLE_ADMIN,
                'phone' => '+1234567890',
                'email_verified_at' => now(),
            ]
        );

        // Crear cliente de prueba
        User::updateOrCreate(
            ['email' => 'cliente@example.com'],
            [
                'name' => 'Cliente de Prueba',
                'email' => 'cliente@example.com',
                'password' => Hash::make('cliente123'),
                'role' => User::ROLE_CLIENTE,
                'phone' => '+0987654321',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Usuarios con roles en español creados exitosamente!');
        $this->command->info('SuperAdmin: superadmin@restaurant.com / super123');
        $this->command->info('Admin: admin@restaurant.com / admin123');
        $this->command->info('Cliente: cliente@example.com / cliente123');
    }
}