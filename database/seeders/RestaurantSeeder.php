<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Restaurant;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        // Restaurante Japonés
        Restaurant::create([
            'name' => 'Sakura Sushi House',
            'description' => 'Auténtica cocina japonesa con sushi fresco y ambiente tradicional.',
            'address' => 'Av. Oriental 123, Centro',
            'phone' => '+1234567800',
            'email' => 'info@sakurasushi.com',
            'opening_time' => '11:00:00',
            'closing_time' => '23:00:00',
            'opening_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'max_capacity' => 80,
            'cuisine_type' => 'japonesa',
            'is_active' => true,
        ]);

        // Restaurante Chino
        Restaurant::create([
            'name' => 'Dragon Golden Palace',
            'description' => 'Comida china tradicional con platos cantoneses y dim sum.',
            'address' => 'Calle Imperial 456, Zona Rosa',
            'phone' => '+1234567801',
            'email' => 'info@dragonpalace.com',
            'opening_time' => '12:00:00',
            'closing_time' => '22:00:00',
            'opening_days' => ['tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'max_capacity' => 120,
            'cuisine_type' => 'china',
            'is_active' => true,
        ]);

        // Restaurante Coreano
        Restaurant::create([
            'name' => 'Seoul Kitchen BBQ',
            'description' => 'Barbacoa coreana y platos tradicionales en un ambiente moderno.',
            'address' => 'Boulevard Asia 789, Nueva Ciudad',
            'phone' => '+1234567802',
            'email' => 'info@seoulkitchen.com',
            'opening_time' => '17:00:00',
            'closing_time' => '24:00:00',
            'opening_days' => ['wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'max_capacity' => 60,
            'cuisine_type' => 'coreana',
            'is_active' => true,
        ]);
    }
}