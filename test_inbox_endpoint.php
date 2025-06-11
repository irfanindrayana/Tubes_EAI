<?php

echo "=== TESTING INBOX ENDPOINT ===\n\n";

// Test HTTP request to inbox endpoint
try {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'header' => [
                'User-Agent: Mozilla/5.0 (compatible; Test)',
            ]
        ]
    ]);
    
    echo "1. Testing endpoint accessibility...\n";
    $response = file_get_contents('http://127.0.0.1:8000/inbox', false, $context);
    
    if ($response !== false) {
        echo "✅ Endpoint accessible - Response received\n";
        
        // Check if response contains error patterns
        if (strpos($response, 'Missing required parameter') !== false) {
            echo "❌ Still has parameter error\n";
        } elseif (strpos($response, 'Undefined variable') !== false) {
            echo "❌ Still has undefined variable error\n";
        } elseif (strpos($response, 'Internal Server Error') !== false) {
            echo "❌ Has internal server error\n";
        } else {
            echo "✅ No obvious errors detected in response\n";
        }
    } else {
        echo "❌ Could not reach endpoint\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error accessing endpoint: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETED ===\n";
