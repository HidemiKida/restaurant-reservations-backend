<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create superadmin user
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@restaurant.com'],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make('SuperAdmin123!'),
            ]
        );
        $superadmin->assignRole(Role::SUPERADMIN);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@restaurant.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('Admin123!'),
            ]
        );
        $admin->assignRole(Role::ADMIN);

        // Create client user
        $client = User::firstOrCreate(
            ['email' => 'cliente@test.com'],
            [
                'name' => 'Cliente de Prueba',
                'password' => Hash::make('Cliente123!'),
            ]
        );
        $client->assignRole(Role::CLIENT);

        $this->command->info('Usuarios de prueba creados exitosamente.');
        $this->command->info('SuperAdmin: superadmin@restaurant.com / SuperAdmin123!');
        $this->command->info('Admin: admin@restaurant.com / Admin123!');
        $this->command->info('Cliente: cliente@test.com / Cliente123!');
    }
}