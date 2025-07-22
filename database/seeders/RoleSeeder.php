<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => Role::CLIENT,
                'description' => 'Usuario cliente del sistema, puede hacer reservaciones'
            ],
            [
                'name' => Role::ADMIN,
                'description' => 'Administrador del sistema, puede gestionar reservaciones y usuarios'
            ],
            [
                'name' => Role::SUPERADMIN,
                'description' => 'Super administrador del sistema, acceso completo'
            ]
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']], 
                $role
            );
        }

        $this->command->info('Roles creados exitosamente.');
    }
}