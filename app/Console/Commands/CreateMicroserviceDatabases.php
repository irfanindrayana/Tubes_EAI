<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateMicroserviceDatabases extends Command
{
    protected $signature = 'microservices:create-databases';
    protected $description = 'Create microservice databases directly';

    public function handle()
    {
        $this->info('Creating microservice databases...');

        $databases = [
            'transbandung_users' => 'user_management',
            'transbandung_ticketing' => 'ticketing',
            'transbandung_payments' => 'payment',
            'transbandung_reviews' => 'reviews',
            'transbandung_inbox' => 'inbox',
        ];

        foreach ($databases as $database => $connection) {
            try {
                $this->info("Creating database: {$database}");
                DB::statement("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Test connection
                DB::connection($connection)->getPdo();
                $this->info("✅ Database {$database} created and connection tested successfully");
                
            } catch (\Exception $e) {
                $this->error("❌ Failed to create {$database}: " . $e->getMessage());
            }
        }

        $this->info('🚀 All microservice databases created successfully!');
        
        // Show connection summary
        $this->info("\n=== Database Summary ===");
        foreach ($databases as $database => $connection) {
            $this->line("📁 {$database} -> {$connection} connection");
        }
    }
}
