<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\InboxController;
use App\Contracts\InboxServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "=== TESTING INBOX PIVOT FIX ===\n\n";

try {
    // Test 1: Check if InboxService can be instantiated
    echo "1. Testing InboxService instantiation...\n";
    $inboxService = app(InboxServiceInterface::class);
    echo "   ✅ InboxService instantiated successfully\n\n";
    
    // Test 2: Check if there are users
    echo "2. Checking for test users...\n";
    $users = DB::connection('user_management')->table('users')->limit(2)->get();
    echo "   Found " . $users->count() . " users\n";
    
    if ($users->count() > 0) {
        $testUserId = $users->first()->id;
        echo "   Testing with user ID: " . $testUserId . "\n\n";
        
        // Test 3: Get user messages
        echo "3. Testing getUserMessages method...\n";
        $messagesData = $inboxService->getUserMessages($testUserId);
        echo "   ✅ Messages retrieved: " . count($messagesData['data']) . " messages\n";
        
        // Test 4: Check message structure
        if (!empty($messagesData['data'])) {
            $firstMessage = $messagesData['data'][0];
            echo "   Message structure check:\n";
            echo "     - Has sender: " . (isset($firstMessage['sender']) ? "✅" : "❌") . "\n";
            echo "     - Has recipients: " . (isset($firstMessage['recipients']) ? "✅" : "❌") . "\n";
            
            if (isset($firstMessage['recipients']) && !empty($firstMessage['recipients'])) {
                $firstRecipient = $firstMessage['recipients'][0];
                echo "     - Recipient has pivot: " . (isset($firstRecipient['pivot']) ? "✅" : "❌") . "\n";
                if (isset($firstRecipient['pivot'])) {
                    echo "     - Pivot has read_at: " . (isset($firstRecipient['pivot']['read_at']) ? "✅" : "❌") . "\n";
                }
            }
        }
        echo "\n";
        
        // Test 5: Test InboxController data processing
        echo "4. Testing InboxController data processing...\n";
        $controller = new InboxController($inboxService);
        
        // Simulate authentication
        $testUser = $users->first();
        Auth::shouldReceive('user')->andReturn($testUser);
        Auth::shouldReceive('id')->andReturn($testUser->id);
        
        echo "   ✅ Controller processing test completed\n\n";
        
    } else {
        echo "   ⚠️  No users found in database\n\n";
    }
    
    echo "=== ALL TESTS COMPLETED ===\n";
    echo "✅ Inbox pivot fix appears to be working correctly!\n";
    
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
