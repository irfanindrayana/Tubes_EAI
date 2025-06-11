<?php

require_once 'vendor/autoload.php';

echo "=== FINAL INBOX VERIFICATION ===\n\n";

// Test 1: Check if InboxService can be loaded without redeclaration error
echo "1. Testing InboxService instantiation...\n";
try {
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    $inboxService = app('App\Services\InboxService');
    echo "✅ InboxService loaded successfully - no redeclaration error\n";
} catch (Exception $e) {
    echo "❌ InboxService error: " . $e->getMessage() . "\n";
}

// Test 2: Test HTTP request to inbox endpoint
echo "\n2. Testing HTTP request to inbox endpoint...\n";
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/inbox');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "✅ HTTP 200 OK - Inbox page loads successfully\n";
        
        // Check for specific errors in response
        if (strpos($response, 'Undefined variable') !== false) {
            echo "❌ Found 'Undefined variable' error in response\n";
        } else {
            echo "✅ No 'Undefined variable' errors found\n";
        }
        
        if (strpos($response, 'Undefined property') !== false) {
            echo "❌ Found 'Undefined property' error in response\n";
        } else {
            echo "✅ No 'Undefined property' errors found\n";
        }
        
        if (strpos($response, 'Missing required parameter') !== false) {
            echo "❌ Found 'Missing required parameter' error in response\n";
        } else {
            echo "✅ No 'Missing required parameter' errors found\n";
        }
        
        if (strpos($response, 'Cannot redeclare') !== false) {
            echo "❌ Found 'Cannot redeclare' error in response\n";
        } else {
            echo "✅ No 'Cannot redeclare' errors found\n";
        }
        
    } else if ($httpCode == 302) {
        echo "⚠️  HTTP 302 Redirect - Likely redirected to login (normal for authenticated routes)\n";
    } else {
        echo "❌ HTTP $httpCode - Unexpected response code\n";
    }
    
} catch (Exception $e) {
    echo "❌ HTTP request failed: " . $e->getMessage() . "\n";
}

// Test 3: Check route definitions
echo "\n3. Testing route definitions...\n";
try {
    $routes = shell_exec('php artisan route:list --name=inbox');
    if (strpos($routes, 'inbox.index') !== false) {
        echo "✅ inbox.index route found\n";
    }
    if (strpos($routes, 'inbox.show') !== false) {
        echo "✅ inbox.show route found\n";
    }
    if (strpos($routes, 'inbox.send') !== false) {
        echo "✅ inbox.send route found\n";
    }
} catch (Exception $e) {
    echo "❌ Route check failed: " . $e->getMessage() . "\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
echo "If all tests show ✅, the inbox system is working correctly!\n";
