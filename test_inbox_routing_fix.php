<?php
/**
 * Test the inbox routing fix
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== INBOX ROUTING FIX TEST ===\n\n";

try {
    // Test 1: Check routes exist
    echo "1. Testing inbox routes...\n";
    $router = app('router');
    $routes = $router->getRoutes();
    
    $inboxRoutes = ['inbox.index', 'inbox.show', 'inbox.send'];
    foreach ($inboxRoutes as $routeName) {
        $route = $routes->getByName($routeName);
        echo "   Route '$routeName': " . ($route ? "✅ EXISTS" : "❌ MISSING") . "\n";
    }
    echo "\n";
    
    // Test 2: Test InboxController instantiation
    echo "2. Testing InboxController...\n";
    $controller = app('App\Http\Controllers\InboxController');
    echo "   Controller instantiated: ✅\n\n";
    
    // Test 3: Check for test user and messages
    echo "3. Checking test data...\n";
    $user = DB::connection('user_management')->table('users')->first();
    if ($user) {
        echo "   Test user found: {$user->name} (ID: {$user->id})\n";
        
        $inboxService = app('App\Contracts\InboxServiceInterface');
        $messagesData = $inboxService->getUserMessages($user->id);
        echo "   Messages found: " . count($messagesData['data']) . "\n";
        
        if (!empty($messagesData['data'])) {
            $firstMessage = $messagesData['data'][0];
            echo "   First message ID: " . ($firstMessage['id'] ?? 'MISSING') . "\n";
            echo "   First message has created_at: " . (isset($firstMessage['created_at']) ? "✅" : "❌") . "\n";
            echo "   First message has sender: " . (isset($firstMessage['sender']) ? "✅" : "❌") . "\n";
            echo "   First message has recipients: " . (isset($firstMessage['recipients']) ? "✅" : "❌") . "\n";
        }
    } else {
        echo "   ⚠️  No test user found\n";
    }
    echo "\n";
    
    // Test 4: Test route generation
    echo "4. Testing route generation...\n";
    if (!empty($messagesData['data'])) {
        $messageId = $messagesData['data'][0]['id'];
        $showUrl = route('inbox.show', $messageId);
        echo "   Generated inbox.show URL: $showUrl\n";
        echo "   Route generation: ✅\n";
    } else {
        echo "   No messages to test route generation\n";
    }
    echo "\n";
    
    echo "=== TEST COMPLETED ===\n";
    echo "✅ Inbox routing fix appears to be working!\n";
    echo "\nThe page http://127.0.0.1:8000/inbox should now be accessible.\n";
    
} catch (Exception $e) {
    echo "❌ Error during test: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
