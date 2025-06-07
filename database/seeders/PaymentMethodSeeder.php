<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'Bank BCA',
                'code' => 'BCA_TRANSFER',
                'description' => 'Transfer Bank BCA',
                'configuration' => json_encode([
                    'type' => 'bank_transfer',
                    'account_number' => '1234567890',
                    'account_name' => 'PT Trans Bandung',
                    'bank_name' => 'Bank Central Asia',
                    'instructions' => 'Transfer ke rekening BCA 1234567890 atas nama PT Trans Bandung. Setelah transfer, upload bukti pembayaran.'
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'Bank Mandiri',
                'code' => 'MANDIRI_TRANSFER',
                'description' => 'Transfer Bank Mandiri',
                'configuration' => json_encode([
                    'type' => 'bank_transfer',
                    'account_number' => '9876543210',
                    'account_name' => 'PT Trans Bandung',
                    'bank_name' => 'Bank Mandiri',
                    'instructions' => 'Transfer ke rekening Mandiri 9876543210 atas nama PT Trans Bandung. Setelah transfer, upload bukti pembayaran.'
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'OVO',
                'code' => 'OVO_EWALLET',
                'description' => 'OVO E-Wallet',
                'configuration' => json_encode([
                    'type' => 'e_wallet',
                    'account_number' => '08123456789',
                    'account_name' => 'PT Trans Bandung',
                    'provider' => 'OVO',
                    'instructions' => 'Transfer ke OVO 08123456789 atas nama PT Trans Bandung. Screenshot hasil transfer sebagai bukti pembayaran.'
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'GoPay',
                'code' => 'GOPAY_EWALLET',
                'description' => 'GoPay E-Wallet',
                'configuration' => json_encode([
                    'type' => 'e_wallet',
                    'account_number' => '08123456789',
                    'account_name' => 'PT Trans Bandung',
                    'provider' => 'GoPay',
                    'instructions' => 'Transfer ke GoPay 08123456789 atas nama PT Trans Bandung. Screenshot hasil transfer sebagai bukti pembayaran.'
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'DANA',
                'code' => 'DANA_EWALLET',
                'description' => 'DANA E-Wallet',
                'configuration' => json_encode([
                    'type' => 'e_wallet',
                    'account_number' => '08123456789',
                    'account_name' => 'PT Trans Bandung',
                    'provider' => 'DANA',
                    'instructions' => 'Transfer ke DANA 08123456789 atas nama PT Trans Bandung. Screenshot hasil transfer sebagai bukti pembayaran.'
                ]),
                'is_active' => true,
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::create($method);
        }

        $this->command->info('Payment methods created successfully!');
    }
}
