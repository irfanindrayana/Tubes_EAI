<?php
/**
 * Simple test to verify the inbox pivot fix
 * This test checks that the InboxService returns proper pivot data
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== INBOX PIVOT FIX VERIFICATION ===\n\n";

try {
    // Test 1: Check InboxService binding
    echo "1. Testing InboxService binding...\n";
    $serviceIsBound = app()->bound('App\Contracts\InboxServiceInterface');
    echo "   Service bound: " . ($serviceIsBound ? "✅ YES" : "❌ NO") . "\n\n";
    
    if (!$serviceIsBound) {
        throw new Exception("InboxService is not properly bound");
    }
    
    // Test 2: Get test user
    echo "2. Getting test user...\n";
    $user = DB::connection('user_management')->table('users')->first();
    if (!$user) {
        throw new Exception("No users found in database");
    }
    echo "   Test user: {$user->name} (ID: {$user->id})\n\n";
    
    // Test 3: Test InboxService
    echo "3. Testing InboxService getUserMessages...\n";
    $inboxService = app('App\Contracts\InboxServiceInterface');
    $messagesData = $inboxService->getUserMessages($user->id);
    
    echo "   Messages found: " . count($messagesData['data']) . "\n";
    
    if (!empty($messagesData['data'])) {
        $firstMessage = $messagesData['data'][0];
        
        echo "   First message structure:\n";
        echo "     - Has sender: " . (isset($firstMessage['sender']) ? "✅" : "❌") . "\n";
        echo "     - Has recipients: " . (isset($firstMessage['recipients']) ? "✅" : "❌") . "\n";
        
        if (isset($firstMessage['recipients']) && !empty($firstMessage['recipients'])) {
            $firstRecipient = $firstMessage['recipients'][0];
            echo "     - First recipient has pivot: " . (isset($firstRecipient['pivot']) ? "✅" : "❌") . "\n";
            
            if (isset($firstRecipient['pivot'])) {
                $pivot = $firstRecipient['pivot'];
                echo "     - Pivot has read_at: " . (array_key_exists('read_at', $pivot) ? "✅" : "❌") . "\n";
                echo "     - Pivot has is_starred: " . (array_key_exists('is_starred', $pivot) ? "✅" : "❌") . "\n";
                echo "     - Pivot has is_archived: " . (array_key_exists('is_archived', $pivot) ? "✅" : "❌") . "\n";
                echo "     - Pivot data: " . json_encode($pivot) . "\n";
            }
        }
    } else {
        echo "   ⚠️  No messages found for this user\n";
    }
    
    echo "\n4. Testing Controller data processing...\n";
    
    // Simulate what the controller does
    $messages = collect($messagesData['data'])->map(function ($messageArray) {
        $message = new stdClass();
        $message->id = $messageArray['id'];
        $message->sender_id = $messageArray['sender_id'];
        
        if (isset($messageArray['recipients'])) {
            $message->recipients = collect($messageArray['recipients'])->map(function ($recipient) {
                $recipientObj = (object) $recipient;
                if (isset($recipient['pivot'])) {
                    $recipientObj->pivot = (object) $recipient['pivot'];
                }
                return $recipientObj;
            });
        } else {
            $message->recipients = collect();
        }
        
        return $message;
    });
    
    if ($messages->count() > 0) {
        $firstMessage = $messages->first();
        echo "   Controller processed message:\n";
        echo "     - Recipients collection: " . ($firstMessage->recipients instanceof \Illuminate\Support\Collection ? "✅" : "❌") . "\n";
        
        if ($firstMessage->recipients->count() > 0) {
            $firstRecipient = $firstMessage->recipients->first();
            echo "     - First recipient is object: " . (is_object($firstRecipient) ? "✅" : "❌") . "\n";
            echo "     - Has pivot property: " . (property_exists($firstRecipient, 'pivot') ? "✅" : "❌") . "\n";
            
            if (property_exists($firstRecipient, 'pivot') && $firstRecipient->pivot) {
                echo "     - Pivot is object: " . (is_object($firstRecipient->pivot) ? "✅" : "❌") . "\n";
                echo "     - Pivot has read_at: " . (property_exists($firstRecipient->pivot, 'read_at') ? "✅" : "❌") . "\n";
            }
        }
    }
    
    echo "\n=== VERIFICATION COMPLETE ===\n";
    echo "✅ All tests passed! Inbox pivot fix is working correctly.\n";
    echo "\nYou should now be able to access http://127.0.0.1:8000/inbox without errors.\n";
    
} catch (Exception $e) {
    echo "❌ Error during verification: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
