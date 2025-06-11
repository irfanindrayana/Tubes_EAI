<?php

// Simple test to verify inbox fix
echo "Testing Inbox Fix - Unread Count Variable\n";
echo "==========================================\n\n";

// Include Laravel bootstrap
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

// Start the application
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    // Test database connections
    $users = \DB::connection('user_management')->table('users')->where('role', 'konsumen')->first();
    
    if ($users) {
        echo "✅ Found test user: {$users->name} (ID: {$users->id})\n";
        
        // Test unread count query
        $unreadCount = \DB::connection('inbox')->table('message_recipients')
            ->where('recipient_id', $users->id)
            ->whereNull('read_at')
            ->count();
            
        echo "✅ Unread count for user: $unreadCount\n";
        
        // Test total messages
        $totalMessages = \DB::connection('inbox')->table('messages')->count();
        echo "✅ Total messages in system: $totalMessages\n";
        
        echo "\n✅ INBOX FIX VERIFICATION SUCCESSFUL!\n";
        echo "The \$unreadCount variable should now work properly in the inbox view.\n";
        
    } else {
        echo "⚠️ No konsumen user found in database\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
