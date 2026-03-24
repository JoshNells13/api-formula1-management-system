<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\Driver;
use App\Models\Race;
use App\Models\Team;
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
            'email' => 'admin@f1.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Race Engineer',
            'email' => 'engineer@f1.com',
            'password' => Hash::make('password'),
            'role' => 'engineer',
        ]);

        User::create([
            'name' => 'Viewer',
            'email' => 'viewer@f1.com',
            'password' => Hash::make('password'),
            'role' => 'viewer',
        ]);

        // 2. Teams
        $ferrari = Team::create([
            'name' => 'Scuderia Ferrari',
            'country' => 'Italy',
            'principal' => 'Frédéric Vasseur',
            'base' => 'Maranello, Italy',
            'status' => 'active',
        ]);

        $mercedes = Team::create([
            'name' => 'Mercedes-AMG Petronas',
            'country' => 'United Kingdom',
            'principal' => 'Toto Wolff',
            'base' => 'Brackley, United Kingdom',
            'status' => 'active',
        ]);

        $redbull = Team::create([
            'name' => 'Oracle Red Bull Racing',
            'country' => 'Austria',
            'principal' => 'Christian Horner',
            'base' => 'Milton Keynes, United Kingdom',
            'status' => 'active',
        ]);

        // 3. Drivers
        // --- Ferrari ---
        Driver::create([
            'team_id' => $ferrari->id,
            'name' => 'Charles Leclerc',
            'number' => 16,
            'nationality' => 'Monegasque',
            'speed' => 95,
            'consistency' => 87,
            'tyre_management' => 80,
            'status' => 'active',
        ]);

        Driver::create([
            'team_id' => $ferrari->id,
            'name' => 'Carlos Sainz',
            'number' => 55,
            'nationality' => 'Spanish',
            'speed' => 89,
            'consistency' => 92,
            'tyre_management' => 88,
            'status' => 'active',
        ]);

        // --- Mercedes ---
        Driver::create([
            'team_id' => $mercedes->id,
            'name' => 'Lewis Hamilton',
            'number' => 44,
            'nationality' => 'British',
            'speed' => 99,
            'consistency' => 98,
            'tyre_management' => 95,
            'status' => 'active',
        ]);

        Driver::create([
            'team_id' => $mercedes->id,
            'name' => 'George Russell',
            'number' => 63,
            'nationality' => 'British',
            'speed' => 91,
            'consistency' => 89,
            'tyre_management' => 85,
            'status' => 'active',
        ]);

        // --- Red Bull ---
        Driver::create([
            'team_id' => $redbull->id,
            'name' => 'Max Verstappen',
            'number' => 1,
            'nationality' => 'Dutch',
            'speed' => 99,
            'consistency' => 97,
            'tyre_management' => 94,
            'status' => 'active',
        ]);

        Driver::create([
            'team_id' => $redbull->id,
            'name' => 'Sergio Perez',
            'number' => 11,
            'nationality' => 'Mexican',
            'speed' => 86,
            'consistency' => 84,
            'tyre_management' => 87,
            'status' => 'active',
        ]);

        // 4. Cars (one per team)
        Car::create([
            'team_id' => $ferrari->id,
            'name' => 'Ferrari SF-24',
            'chassis_code' => 'SF-24-01',
            'top_speed' => 92,
            'acceleration' => 90,
            'downforce' => 88,
            'reliability' => 85,
        ]);

        Car::create([
            'team_id' => $mercedes->id,
            'name' => 'Mercedes W15',
            'chassis_code' => 'W15-01',
            'top_speed' => 91,
            'acceleration' => 89,
            'downforce' => 90,
            'reliability' => 93,
        ]);

        Car::create([
            'team_id' => $redbull->id,
            'name' => 'Red Bull RB20',
            'chassis_code' => 'RB20-01',
            'top_speed' => 96,
            'acceleration' => 95,
            'downforce' => 93,
            'reliability' => 90,
        ]);

        // 5. Races
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

