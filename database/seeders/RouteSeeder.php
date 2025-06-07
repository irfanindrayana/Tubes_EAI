<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\Seat;

class RouteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $routes = [
            [
                'route_name' => 'Trans Bandung Route 1',
                'origin' => 'Terminal Leuwi Panjang',
                'destination' => 'Terminal Cicaheum',
                'stops' => json_encode(['Stasiun Hall', 'Alun-alun', 'Pasar Baru']),
                'distance' => 15.5,
                'base_price' => 5000,
            ],
            [
                'route_name' => 'Trans Bandung Route 2',
                'origin' => 'Terminal Cicaheum',
                'destination' => 'Terminal Leuwi Panjang',
                'stops' => json_encode(['Pasar Baru', 'Alun-alun', 'Stasiun Hall']),
                'distance' => 15.5,
                'base_price' => 5000,
            ],
            [
                'route_name' => 'Trans Bandung Route 3',
                'origin' => 'Terminal Caringin',
                'destination' => 'Terminal Cimindi',
                'stops' => json_encode(['Pasteur', 'Dago', 'ITB']),
                'distance' => 12.3,
                'base_price' => 4000,
            ],
            [
                'route_name' => 'Trans Bandung Route 4',
                'origin' => 'Terminal Cimindi',
                'destination' => 'Terminal Caringin',
                'stops' => json_encode(['ITB', 'Dago', 'Pasteur']),
                'distance' => 12.3,
                'base_price' => 4000,
            ],
        ];

        foreach ($routes as $routeData) {
            $route = Route::create($routeData);

            // Create schedules for each route
            $scheduleTimes = [
                ['06:00', '06:45'],
                ['07:00', '07:45'],
                ['08:00', '08:45'],
                ['09:00', '09:45'],
                ['10:00', '10:45'],
                ['11:00', '11:45'],
                ['12:00', '12:45'],
                ['13:00', '13:45'],
                ['14:00', '14:45'],
                ['15:00', '15:45'],
                ['16:00', '16:45'],
                ['17:00', '17:45'],
            ];

            foreach ($scheduleTimes as $index => $times) {
                $schedule = Schedule::create([
                    'route_id' => $route->id,
                    'bus_code' => 'TBA-' . $route->id . '-' . sprintf('%02d', $index + 1),
                    'departure_time' => $times[0] . ':00',
                    'arrival_time' => $times[1] . ':00',
                    'total_seats' => 40,
                    'available_seats' => 40,
                    'days_of_week' => json_encode([1, 2, 3, 4, 5, 6, 0]), // Monday to Sunday (1-6, 0)
                    'price' => $route->base_price,
                    'is_active' => true,
                ]);

                // Create seats for the schedule for today and tomorrow
                for ($day = 0; $day < 2; $day++) {
                    $travelDate = now()->addDays($day)->format('Y-m-d');
                    for ($i = 1; $i <= 40; $i++) {
                        Seat::create([
                            'schedule_id' => $schedule->id,
                            'seat_number' => 'A' . str_pad($i, 2, '0', STR_PAD_LEFT),
                            'travel_date' => $travelDate,
                            'status' => 'available',
                        ]);
                    }
                }
            }
        }

        $this->command->info('Routes, schedules, and seats created successfully!');
    }
}
