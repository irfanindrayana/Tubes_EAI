<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Booking;
use App\Models\Route;
use Illuminate\Support\Str;

class ReviewComplaintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sample data
        $users = User::where('role', 'konsumen')->take(10)->get();
        $bookings = Booking::where('status', 'completed')->orWhere('status', 'confirmed')->take(15)->get();
        $routes = Route::where('is_active', true)->get();

        if ($users->isEmpty() || $bookings->isEmpty() || $routes->isEmpty()) {
            $this->command->error('Please run other seeders first (users, bookings, routes)!');
            return;
        }

        // Sample review comments
        $reviewComments = [
            'Pelayanan sangat baik, bus nyaman dan tepat waktu!',
            'Driver ramah dan bus bersih. Recommended!',
            'Perjalanan menyenangkan, akan naik lagi next time.',
            'Bus nyaman tapi agak telat 15 menit.',
            'Overall good experience, bus dalam kondisi baik.',
            'Pelayanan ok, hanya AC kurang dingin.',
            'Sangat puas dengan pelayanannya.',
            'Bus bersih dan driver profesional.',
            'Perjalanan lancar dan aman.',
            'Sesuai ekspektasi, akan rekomendasikan ke teman.'
        ];

        // Sample complaint subjects and descriptions
        $complaints = [
            [
                'subject' => 'Bus Terlambat 30 Menit',
                'description' => 'Bus dengan kode TBA-001 terlambat 30 menit dari jadwal keberangkatan. Mohon diperbaiki ketepatan waktu.',
                'category' => 'service',
                'priority' => 'medium'
            ],
            [
                'subject' => 'AC Bus Tidak Dingin',
                'description' => 'Selama perjalanan AC bus tidak dingin, membuat perjalanan kurang nyaman terutama di siang hari.',
                'category' => 'service',
                'priority' => 'low'
            ],
            [
                'subject' => 'Payment Verification Lama',
                'description' => 'Sudah upload bukti transfer sejak kemarin tapi payment belum diverifikasi. Mohon dipercepat prosesnya.',
                'category' => 'payment',
                'priority' => 'high'
            ],
            [
                'subject' => 'Website Error saat Booking',
                'description' => 'Website mengalami error 500 saat proses booking tiket. Terpaksa harus refresh berkali-kali.',
                'category' => 'technical',
                'priority' => 'medium'
            ],
            [
                'subject' => 'Seat yang Dibooking Tidak Sesuai',
                'description' => 'Sudah booking seat A01 tapi dikasih seat A05. Mohon dipastikan seat sesuai dengan booking.',
                'category' => 'service',
                'priority' => 'high'
            ]
        ];

        // Create reviews
        foreach ($bookings->take(10) as $booking) {
            if (rand(1, 10) > 3) { // 70% chance of having a review
                Review::create([
                    'user_id' => $booking->user_id,
                    'booking_id' => $booking->id,
                    'route_id' => $booking->schedule->route_id,
                    'rating' => rand(3, 5), // Generally positive ratings
                    'comment' => $reviewComments[array_rand($reviewComments)],
                    'aspects_rating' => [
                        'punctuality' => rand(3, 5),
                        'comfort' => rand(3, 5),
                        'cleanliness' => rand(3, 5),
                        'service' => rand(3, 5)
                    ],
                    'status' => 'active',
                    'reviewed_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }

        // Create complaints
        foreach ($complaints as $index => $complaintData) {
            $user = $users->random();
            $booking = $bookings->random();

            Complaint::create([
                'complaint_code' => 'COMP-' . strtoupper(Str::random(6)),
                'user_id' => $user->id,
                'booking_id' => rand(1, 10) > 3 ? $booking->id : null, // 70% chance related to booking
                'subject' => $complaintData['subject'],
                'description' => $complaintData['description'],
                'category' => $complaintData['category'],
                'priority' => $complaintData['priority'],
                'status' => ['open', 'in_progress', 'resolved'][array_rand(['open', 'in_progress', 'resolved'])],
                'submitted_at' => now()->subDays(rand(1, 14)),
                'resolved_at' => rand(1, 10) > 5 ? now()->subDays(rand(1, 7)) : null, // 50% chance resolved
                'assigned_to' => User::where('role', 'admin')->first()->id,
                'resolution_notes' => rand(1, 10) > 5 ? 'Issue has been resolved. Thank you for your feedback.' : null,
            ]);
        }

        $this->command->info('Reviews and complaints created successfully!');
    }
}
