<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class SetupMicroservicesDatabases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'microservices:setup-databases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and migrate databases for all microservices';

    public function handle()
    {
        $this->info('Setting up microservices databases...');

        $databases = [
            'user_management' => 'transbandung_users',
            'ticketing' => 'transbandung_ticketing',
            'payment' => 'transbandung_payments',
            'reviews' => 'transbandung_reviews',
            'inbox' => 'transbandung_inbox',
        ];

        foreach ($databases as $connection => $database) {
            $this->info("Setting up database: {$database}");
            
            try {
                // Create database if it doesn't exist
                $this->createDatabase($database);
                
                // Run migrations for specific connection
                $this->migrateDatabaseConnection($connection);
                
                $this->info("✅ Database {$database} setup completed");
            } catch (\Exception $e) {
                $this->error("❌ Failed to setup {$database}: " . $e->getMessage());
            }
        }

        $this->info('🚀 All microservices databases setup completed!');
        
        // Seed the databases
        $this->info('Seeding databases...');
        $this->seedDatabases();
    }

    protected function createDatabase($database)
    {
        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->info("Database {$database} created or already exists");
        } catch (\Exception $e) {
            $this->warn("Could not create database {$database}: " . $e->getMessage());
        }
    }    protected function migrateDatabaseConnection($connection)
    {
        $migrations = $this->getMigrationsForConnection($connection);
        
        foreach ($migrations as $migration) {
            try {
                $this->info("Running migration {$migration} on {$connection}");
                Artisan::call('migrate', [
                    '--database' => $connection,
                    '--path' => "database/migrations",
                    '--force' => true
                ]);
                break; // Run all migrations for the connection at once
            } catch (\Exception $e) {
                $this->warn("Migration {$migration} failed: " . $e->getMessage());
            }
        }
    }protected function getMigrationsForConnection($connection)
    {
        $migrations = [
            'user_management' => [
                '2025_06_04_140004_create_user_management_microservice_tables',
            ],
            'ticketing' => [
                '2025_06_04_140000_create_ticketing_microservice_tables',
            ],
            'payment' => [
                '2025_06_04_140001_create_payment_microservice_tables',
            ],
            'reviews' => [
                '2025_06_04_140002_create_reviews_microservice_tables',
            ],
            'inbox' => [
                '2025_06_04_140003_create_inbox_microservice_tables',
            ],
        ];

        return $migrations[$connection] ?? [];
    }

    protected function seedDatabases()
    {
        try {
            Artisan::call('db:seed', ['--force' => true]);
            $this->info('✅ Database seeding completed');
        } catch (\Exception $e) {
            $this->error('❌ Database seeding failed: ' . $e->getMessage());
        }
    }
}
