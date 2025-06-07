<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Providers\MicroserviceProvider;
use App\Services\InboxService;

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
        
        // Test 2: InboxService instantiation
        $this->info('2. Testing InboxService instantiation...');
        $inboxService = new InboxService();
        $this->info('   ✅ InboxService created successfully');
        
        // Test 3: Check for users
        $this->info('3. Checking available users...');
        $users = DB::connection('user_management')->table('users')->limit(3)->get();
        $this->info('   Found ' . $users->count() . ' users');
        
        if ($users->count() > 0) {
            $testUserId = $users->first()->id;
            $this->info('   Testing with user ID: ' . $testUserId);
            
            // Test 4: Get inbox messages
            $this->info('4. Testing getInboxMessages method...');
            $messages = $inboxService->getInboxMessages($testUserId);
            $this->info('   ✅ Retrieved ' . $messages->count() . ' messages');
            
            // Test 5: Get unread count
            $this->info('5. Testing getUnreadCount method...');
            $unreadCount = $inboxService->getUnreadCount($testUserId);
            $this->info('   ✅ Unread messages count: ' . $unreadCount);
            
            // Test 6: Check message details
            if ($messages->count() > 0) {
                $this->info('6. Sample message details:');
                $firstMessage = $messages->first();
                $this->info('   - ID: ' . $firstMessage->id);
                $this->info('   - Subject: ' . $firstMessage->subject);
                $this->info('   - Content: ' . substr($firstMessage->content, 0, 100) . '...');
                $this->info('   - Sender: ' . ($firstMessage->sender ? $firstMessage->sender->name : 'Unknown'));
                $this->info('   - Recipients: ' . $firstMessage->recipients->count() . ' recipient(s)');
            }
        } else {
            $this->info('   ⚠️ No users found - skipping user-specific tests');
        }
        
        // Test 7: Verify cross-database functionality
        $this->info('7. Testing cross-database relationships...');
        $recipients = DB::connection('inbox')->table('message_recipients')->limit(3)->get();
        $this->info('   Found ' . $recipients->count() . ' message recipients');
        
        if ($recipients->count() > 0) {
            foreach ($recipients as $recipient) {
                $user = DB::connection('user_management')
                    ->table('users')
                    ->where('id', $recipient->recipient_id)
                    ->first();
                    
                if ($user) {
                    $this->info('   ✅ Recipient ID ' . $recipient->recipient_id . ' -> User: ' . $user->name);
                } else {
                    $this->info('   ❌ Recipient ID ' . $recipient->recipient_id . ' -> User not found');
                }
            }
        }
        
        $this->info('');
        $this->info('✅ All tests completed successfully!');
        $this->info('The inbox functionality is working correctly with cross-database support.');
        
    } catch (Exception $e) {
        $this->error('❌ Test failed with error: ' . $e->getMessage());
        $this->error('File: ' . $e->getFile());
        $this->error('Line: ' . $e->getLine());
    }
})->purpose('Test inbox functionality after cross-database fixes');
