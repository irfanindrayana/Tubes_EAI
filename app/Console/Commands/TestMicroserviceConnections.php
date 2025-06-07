<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Providers\MicroserviceProvider;

class TestMicroserviceConnections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'microservices:test-connections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all microservice database connections';

    public function handle()
    {
        $this->info('Testing microservice database connections...');
        $this->newLine();

        $connections = [
            'default' => 'Default Database',
            'user_management' => 'User Management Service',
            'ticketing' => 'Ticketing Service', 
            'payment' => 'Payment Service',
            'reviews' => 'Review & Rating Service',
            'inbox' => 'Inbox Service',
        ];

        $results = [];

        foreach ($connections as $connection => $name) {
            $this->info("Testing {$name}...");
            
            try {
                if ($connection === 'default') {
                    DB::connection()->getPdo();
                    $database = DB::connection()->getDatabaseName();
                } else {
                    DB::connection($connection)->getPdo();
                    $database = DB::connection($connection)->getDatabaseName();
                }
                
                $this->line("  ✅ Connection successful");
                $this->line("  📁 Database: {$database}");
                $results[$connection] = ['status' => 'success', 'database' => $database];
                
            } catch (\Exception $e) {
                $this->line("  ❌ Connection failed: " . $e->getMessage());
                $results[$connection] = ['status' => 'failed', 'error' => $e->getMessage()];
            }
            
            $this->newLine();
        }

        // Summary
        $this->info('=== Connection Test Summary ===');
        $successful = 0;
        $failed = 0;

        foreach ($results as $connection => $result) {
            $name = $connections[$connection];
            if ($result['status'] === 'success') {
                $this->line("✅ {$name}: Connected to {$result['database']}");
                $successful++;
            } else {
                $this->line("❌ {$name}: Failed");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Successful connections: {$successful}");
        if ($failed > 0) {
            $this->error("Failed connections: {$failed}");
        }

        // Test microservice status
        $this->newLine();
        $this->info('=== Microservice Status ===');
        $services = MicroserviceProvider::getMicroserviceStatus();
        
        foreach ($services as $key => $service) {
            $this->line("🚀 {$service['name']}: {$service['status']}");
            $this->line("   Database: {$service['database']}");
            $this->line("   Models: " . implode(', ', $service['models']));
            $this->newLine();
        }

        return $failed > 0 ? 1 : 0;
    }
}
