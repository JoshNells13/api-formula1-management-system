<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\Driver;
use App\Models\Race;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users 
        User::create([
            'name' => 'Race Admin',
            'email' => 'admin@ferrari.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Race Engineer',
            'email' => 'engineer@ferrari.com',
            'password' => Hash::make('password'),
            'role' => 'engineer',
        ]);

        User::create([
            'name' => 'Viewer',
            'email' => 'viewer@ferrari.com',
            'password' => Hash::make('password'),
            'role' => 'viewer',
        ]);

        // 2. Drivers 
        $leclerc = Driver::create([
            'name' => 'Charles Leclerc',
            'number' => 16,
            'nationality' => 'Monegasque',
            'speed' => 95,
            'consistency' => 87,
            'tyre_management' => 80,
            'status' => 'active',
        ]);

        $sainz = Driver::create([
            'name' => 'Carlos Sainz',
            'number' => 55,
            'nationality' => 'Spanish',
            'speed' => 89,
            'consistency' => 92,
            'tyre_management' => 88,
            'status' => 'active',
        ]);

        Driver::create([
            'name' => 'Lewis Hamilton',
            'number' => 44,
            'nationality' => 'British',
            'speed' => 99,
            'consistency' => 98,
            'tyre_management' => 95,
            'status' => 'active',
        ]);

        // 3. Cars 
        $sf24 = Car::create([
            'name' => 'Ferrari SF-24',
            'chassis_code' => 'SF-24-01',
            'top_speed' => 92,
            'acceleration' => 90,
            'downforce' => 88,
            'reliability' => 85,
        ]);

        $sf23 = Car::create([
            'name' => 'Ferrari SF-23',
            'chassis_code' => 'SF-23-02',
            'top_speed' => 86,
            'acceleration' => 84,
            'downforce' => 83,
            'reliability' => 88,
        ]);

        // 4. Races 
        Race::create([
            'name' => 'Italian Grand Prix',
            'location' => 'Monza',
            'total_laps' => 53,
            'weather' => 'sunny',
            'race_date' => '2024-09-01',
        ]);

        Race::create([
            'name' => 'Monaco Grand Prix',
            'location' => 'Monte Carlo',
            'total_laps' => 78,
            'weather' => 'cloudy',
            'race_date' => '2024-05-26',
        ]);

        Race::create([
            'name' => 'British Grand Prix',
            'location' => 'Silverstone',
            'total_laps' => 52,
            'weather' => 'rain',
            'race_date' => '2024-07-07',
        ]);
    }
}
