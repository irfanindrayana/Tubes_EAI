<?php
// Simple test to check InboxService pivot fix

require_once 'vendor/autoload.php';

// Initialize Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing InboxService pivot fix...\n";

try {
    // Get first user
    $user = DB::connection('user_management')->table('users')->first();
    
    if ($user) {
        echo "Testing with user: " . $user->name . " (ID: " . $user->id . ")\n";
        
        // Get inbox service
        $inboxService = app(\App\Contracts\InboxServiceInterface::class);
        
        // Get messages
        $messagesData = $inboxService->getUserMessages($user->id);
        
        echo "Found " . count($messagesData['data']) . " messages\n";
        
        if (!empty($messagesData['data'])) {
            $message = $messagesData['data'][0];
            echo "First message has recipients: " . (isset($message['recipients']) ? "YES" : "NO") . "\n";
            
            if (isset($message['recipients']) && !empty($message['recipients'])) {
                $recipient = $message['recipients'][0];
                echo "First recipient has pivot: " . (isset($recipient['pivot']) ? "YES" : "NO") . "\n";
                if (isset($recipient['pivot'])) {
                    echo "Pivot data: " . json_encode($recipient['pivot']) . "\n";
                }
            }
        }
        
        echo "✅ Test completed successfully!\n";
    } else {
        echo "No users found in database\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
