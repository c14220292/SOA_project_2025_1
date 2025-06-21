<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Table;
use App\Models\SlotTime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin Restaurant',
            'email' => 'admin@restaurant.com',
            'phone' => '081234567890',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create Member User
        User::create([
            'name' => 'John Doe',
            'email' => 'member@example.com',
            'phone' => '081234567891',
            'password' => Hash::make('password'),
            'role' => 'member',
        ]);

        // Create Tables (1-20)
        for ($i = 1; $i <= 20; $i++) {
            Table::create([
                'number' => $i,
                'seat_count' => 4,
                'is_available' => true,
            ]);
        }

        // Create Slot Times for next 7 days
        $timeSlots = [
            ['07:00', '08:30'],
            ['09:00', '10:30'],
            ['11:00', '12:30'],
            ['13:00', '14:30'],
            ['15:00', '16:30'],
            ['17:00', '18:30'],
            ['19:00', '20:30'],
        ];

        for ($day = 1; $day <= 7; $day++) {
            $date = now()->addDays($day)->format('Y-m-d');

            foreach ($timeSlots as $slot) {
                SlotTime::create([
                    'start_time' => $slot[0],
                    'end_time' => $slot[1],
                    'date' => $date,
                ]);
            }
        }
    }
}
