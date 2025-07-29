<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario SuperAdmin
        User::create([
            'name' => 'Super Administrador',
            'email' => 'superadmin@restaurant.com',
            'password' => Hash::make('superadmin123'),
            'role' => 'superadmin',
            'phone' => '+1234567890',
            'email_verified_at' => now(),
        ]);

        // Usuario Admin
        User::create([
            'name' => 'Administrador Asiatico',
            'email' => 'admin@restaurant.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'phone' => '+1234567891',
            'email_verified_at' => now(),
        ]);

        // Usuarios Cliente
        User::create([
            'name' => 'Takeshi Yamamoto',
            'email' => 'takeshi@example.com',
            'password' => Hash::make('cliente123'),
            'role' => 'cliente',
            'phone' => '+1234567892',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Li Wei Chen',
            'email' => 'liwei@example.com',
            'password' => Hash::make('cliente123'),
            'role' => 'cliente',
            'phone' => '+1234567893',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Park Jin-Sung',
            'email' => 'parkjin@example.com',
            'password' => Hash::make('cliente123'),
            'role' => 'cliente',
            'phone' => '+1234567894',
            'email_verified_at' => now(),
        ]);
    }
}