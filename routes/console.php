<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Providers\MicroserviceProvider;
use App\Services\Inbox\InboxService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('microservices:test', function () {
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

    $successful = 0;
    $failed = 0;

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
            
            $this->line("  ✅ Connection successful - Database: {$database}");
            $successful++;
            
        } catch (\Exception $e) {
            $this->line("  ❌ Connection failed: " . $e->getMessage());
            $failed++;
        }
        
        $this->newLine();
    }

    $this->info("=== Summary ===");
    $this->info("✅ Successful: {$successful}");
    if ($failed > 0) {
        $this->error("❌ Failed: {$failed}");
    }
    
    // Show microservice status
    $this->newLine();
    $this->info('=== Microservice Status ===');
    $services = MicroserviceProvider::getMicroserviceStatus();
    
    foreach ($services as $key => $service) {
        $this->line("🚀 {$service['name']}: {$service['status']}");
        $this->line("   Database: {$service['database']}");
        $this->line("   Models: " . implode(', ', $service['models']));
        $this->newLine();
    }

})->purpose('Test all microservice database connections');

Artisan::command('test:inbox', function () {
    $this->info('🧪 Testing Inbox Functionality via Artisan');
    $this->info('==========================================');
    
    try {
        // Test 1: Database connections
        $this->info('1. Testing database connections...');
        
        DB::connection('inbox')->getPdo();
        $this->info('   ✅ Inbox database connection successful');
        
        DB::connection('user_management')->getPdo();
        $this->info('   ✅ User management database connection successful');
        
        // Test 2: InboxService instantiation via container
        $this->info('2. Testing InboxService instantiation...');
        $inboxService = app(\App\Contracts\InboxServiceInterface::class);
        $this->info('   ✅ InboxService instantiated via dependency injection');
        
        // Test 3: Check for users
        $this->info('3. Checking available users...');
        $users = DB::connection('user_management')->table('users')->limit(3)->get();
        $this->info('   Found ' . $users->count() . ' users');
        
        if ($users->count() > 0) {
            $testUserId = $users->first()->id;
            $this->info('   Testing with user ID: ' . $testUserId);
            
            // Test 4: Get user messages
            $this->info('4. Testing getUserMessages method...');
            $messagesData = $inboxService->getUserMessages($testUserId);
            $this->info('   ✅ Retrieved messages data successfully');
            
            // Test 5: Get user notifications  
            $this->info('5. Testing getUserNotifications method...');
            $notifications = $inboxService->getUserNotifications($testUserId);
            $this->info('   ✅ Retrieved ' . count($notifications) . ' notifications');
            
            // Test 6: Test UserService via dependency injection
            $this->info('6. Testing UserService instantiation...');
            $userService = app(\App\Contracts\UserServiceInterface::class);
            $basicUserInfo = $userService->getUserBasicInfo($testUserId);
            $this->info('   ✅ UserService working, got user: ' . ($basicUserInfo ? $basicUserInfo['name'] : 'None'));
            
        } else {
            $this->info('   ⚠️ No users found - skipping user-specific tests');
        }
        
        $this->info('');
        $this->info('✅ All service contract tests completed successfully!');
        $this->info('The microservice architecture is working correctly with dependency injection.');
        
    } catch (Exception $e) {
        $this->error('❌ Test failed with error: ' . $e->getMessage());
        $this->error('File: ' . $e->getFile());
        $this->error('Line: ' . $e->getLine());
    }
})->purpose('Test inbox functionality after cross-database fixes');
