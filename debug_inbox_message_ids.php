<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEBUGGING INBOX MESSAGE IDs ===\n\n";

try {
    // Get first user
    $user = DB::connection('user_management')->table('users')->first();
    
    if (!$user) {
        echo "❌ No users found\n";
        exit;
    }
    
    echo "Testing with user: {$user->name} (ID: {$user->id})\n\n";
    
    // Get inbox service
    $inboxService = app(\App\Contracts\InboxServiceInterface::class);
    
    // Get messages data
    $messagesData = $inboxService->getUserMessages($user->id);
    
    echo "Found " . count($messagesData['data']) . " messages\n\n";
    
    foreach ($messagesData['data'] as $index => $message) {
        echo "Message #" . ($index + 1) . ":\n";
        echo "  - ID: " . ($message['id'] ?? 'NULL/MISSING') . "\n";
        echo "  - Subject: " . ($message['subject'] ?? 'NULL/MISSING') . "\n";
        echo "  - Sender ID: " . ($message['sender_id'] ?? 'NULL/MISSING') . "\n";
        
        // Test route generation with this ID
        if (isset($message['id']) && !empty($message['id'])) {
            try {
                $routeUrl = route('inbox.show', $message['id']);
                echo "  - Route URL: " . $routeUrl . " ✅\n";
            } catch (Exception $e) {
                echo "  - Route Error: " . $e->getMessage() . " ❌\n";
            }
        } else {
            echo "  - Route: Cannot generate (missing ID) ❌\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
