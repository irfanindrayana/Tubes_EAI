<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Str;

class MessageNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sample data
        $adminUsers = User::where('role', 'admin')->get();
        $konsumenUsers = User::where('role', 'konsumen')->take(10)->get();

        if ($adminUsers->isEmpty() || $konsumenUsers->isEmpty()) {
            $this->command->error('Please run AdminUserSeeder first!');
            return;
        }

        $admin = $adminUsers->first();

        // Sample messages
        $messages = [
            [
                'subject' => 'Selamat Datang di Trans Bandung',
                'content' => 'Selamat datang di layanan Trans Bandung! Terima kasih telah bergabung dengan kami. Nikmati perjalanan yang nyaman dan aman bersama Trans Bandung.',
                'type' => 'notification',
                'priority' => 'normal'
            ],
            [
                'subject' => 'Promo Spesial Weekend - Diskon 20%',
                'content' => 'Dapatkan diskon 20% untuk semua rute di akhir pekan! Gunakan kode WEEKEND20 saat booking tiket. Promo berlaku mulai Jumat hingga Minggu.',
                'type' => 'promotion',
                'priority' => 'high'
            ],
            [
                'subject' => 'Pemeliharaan Sistem Terjadwal',
                'content' => 'Akan ada pemeliharaan sistem pada Minggu, 15 Desember 2024 pukul 02:00-04:00 WIB. Layanan booking online akan terganggu sementara.',
                'type' => 'announcement',
                'priority' => 'high'
            ],
            [
                'subject' => 'Update Jadwal Rute Terminal Leuwi Panjang',
                'content' => 'Jadwal keberangkatan rute Terminal Leuwi Panjang - Terminal Cicaheum mengalami perubahan. Keberangkatan pertama dimajukan menjadi 05:30 WIB.',
                'type' => 'announcement',
                'priority' => 'normal'
            ],
            [
                'subject' => 'Konfirmasi Pembayaran Berhasil',
                'content' => 'Pembayaran Anda untuk booking TBA-12345 telah berhasil diverifikasi. Tiket elektronik telah dikirim ke email Anda.',
                'type' => 'payment_verification',
                'priority' => 'high'
            ]
        ];

        // Create messages from admin to users
        foreach ($messages as $messageData) {
            $message = Message::create([
                'message_code' => 'MSG-' . strtoupper(Str::random(8)),
                'sender_id' => $admin->id,
                'subject' => $messageData['subject'],
                'content' => $messageData['content'],
                'type' => $messageData['type'],
                'priority' => $messageData['priority'],
                'attachments' => null,
                'sent_at' => now()->subDays(rand(1, 30)),
            ]);

            // Add recipients (broadcast to available konsumen users)
            $maxRecipients = min($konsumenUsers->count(), rand(1, 3)); // Don't exceed available users
            $recipients = $konsumenUsers->random($maxRecipients);
            foreach ($recipients as $recipient) {
                // Use direct DB insert for message_recipients since we're working with multiple DB connections
                \DB::connection('inbox')->table('message_recipients')->insert([
                    'message_id' => $message->id,
                    'recipient_id' => $recipient->id,
                    'read_at' => rand(1, 10) > 3 ? now()->subDays(rand(1, 15)) : null, // 70% read
                    'is_starred' => rand(1, 10) > 8, // 20% starred
                    'is_archived' => rand(1, 10) > 9, // 10% archived
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Sample notifications
        $notifications = [
            [
                'title' => 'Booking Confirmed',
                'content' => 'Your booking TBA-ABC123 has been confirmed. Have a safe trip!',
                'type' => 'booking',
                'data' => ['booking_id' => 1, 'booking_code' => 'TBA-ABC123']
            ],
            [
                'title' => 'Payment Verified',
                'content' => 'Your payment has been successfully verified. Your ticket is now ready.',
                'type' => 'payment',
                'data' => ['payment_id' => 1, 'amount' => 15000]
            ],
            [
                'title' => 'New Promotion Available',
                'content' => 'Check out our weekend promotion with 20% discount!',
                'type' => 'promotion',
                'data' => ['promo_code' => 'WEEKEND20', 'discount' => 20]
            ],
            [
                'title' => 'Schedule Update',
                'content' => 'There are updates to your route schedule. Please check the latest timetable.',
                'type' => 'system',
                'data' => ['route_id' => 1]
            ],
            [
                'title' => 'Trip Reminder',
                'content' => 'Don\'t forget your trip tomorrow at 08:00 AM from Terminal Leuwi Panjang.',
                'type' => 'booking',
                'data' => ['booking_id' => 2, 'departure_time' => '08:00']
            ]
        ];

        // Create notifications for users
        foreach ($konsumenUsers as $user) {
            // Each user gets 2-4 random notifications
            $userNotifications = array_rand($notifications, rand(2, 4));
            if (!is_array($userNotifications)) {
                $userNotifications = [$userNotifications];
            }

            foreach ($userNotifications as $notifIndex) {
                $notifData = $notifications[$notifIndex];
                
                Notification::create([
                    'notification_code' => 'NOTIF-' . strtoupper(Str::random(6)),
                    'user_id' => $user->id,
                    'title' => $notifData['title'],
                    'content' => $notifData['content'],
                    'type' => $notifData['type'],
                    'data' => $notifData['data'],
                    'read_at' => rand(1, 10) > 4 ? now()->subDays(rand(1, 10)) : null, // 60% read
                    'sent_at' => now()->subDays(rand(1, 14)),
                ]);
            }
        }

        $this->command->info('Messages and notifications created successfully!');
    }
}
