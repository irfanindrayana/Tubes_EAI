<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TESTING INBOX WITH AUTHENTICATION ===\n\n";

try {
    // Get a test user
    $user = DB::connection('user_management')->table('users')->first();
    
    if (!$user) {
        echo "❌ No users found for testing\n";
        exit;
    }
    
    echo "Test user: {$user->name} (ID: {$user->id})\n\n";
    
    // Simulate HTTP request with session
    $cookieJar = tempnam(sys_get_temp_dir(), 'cookie');
    
    // First, get the login page to get CSRF token and session
    $loginContext = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: Test Script\r\n"
        ]
    ]);
    
    echo "1. Testing inbox access...\n";
    
    // Direct test to inbox (assuming user is logged in via other means)
    $inboxContext = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 15,
            'header' => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Connection: keep-alive'
            ]
        ]
    ]);
    
    $response = @file_get_contents('http://127.0.0.1:8000/inbox', false, $inboxContext);
    
    if ($response !== false) {
        echo "   ✅ Inbox endpoint accessible\n";
        
        // Check response content
        if (strpos($response, 'Missing required parameter') !== false) {
            echo "   ❌ Still has parameter error in response\n";
            
            // Extract the specific error
            preg_match('/Missing required parameter.*?\[Missing parameter: ([^\]]+)\]/', $response, $matches);
            if (isset($matches[1])) {
                echo "   Missing parameter: {$matches[1]}\n";
            }
        } elseif (strpos($response, 'Internal Server Error') !== false) {
            echo "   ❌ Internal server error detected\n";
        } elseif (strpos($response, 'Messages') !== false || strpos($response, 'Inbox') !== false) {
            echo "   ✅ Inbox page content detected\n";
        } else {
            echo "   ⚠️  Response received but content unclear\n";
        }
        
        // Check response size
        $responseSize = strlen($response);
        echo "   Response size: {$responseSize} bytes\n";
        
        if ($responseSize < 1000) {
            echo "   ⚠️  Response seems too small, might be error page\n";
        }
        
    } else {
        echo "   ❌ Cannot access inbox endpoint\n";
        
        // Check if server is running
        $headers = @get_headers('http://127.0.0.1:8000');
        if ($headers) {
            echo "   ✅ Server is running\n";
            echo "   Response headers: " . $headers[0] . "\n";
        } else {
            echo "   ❌ Server not responding\n";
        }
    }
    
    echo "\n2. Testing routes...\n";
    
    // Test individual route generation
    try {
        $testRoutes = [
            'inbox.index' => route('inbox.index'),
            'inbox.show with ID 1' => route('inbox.show', 1),
            'inbox.send' => route('inbox.send'),
        ];
        
        foreach ($testRoutes as $name => $url) {
            echo "   ✅ {$name}: {$url}\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Route error: " . $e->getMessage() . "\n";
    }
    
    // Clean up
    if (file_exists($cookieJar)) {
        unlink($cookieJar);
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
