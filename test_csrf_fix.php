<?php

// Test GraphQL CSRF Fix
echo "=== GraphQL CSRF Fix Verification ===\n\n";

// Test using cURL to simulate GraphQL Playground request
$url = 'http://127.0.0.1:8000/graphql';

// Test 1: Simple introspection query
echo "1. Testing Introspection Query (should work without CSRF issues)...\n";
$introspectionQuery = json_encode([
    'query' => '{ __schema { queryType { name } } }'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $introspectionQuery);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Response: $response\n\n";

// Test 2: Login mutation (the one that was failing)
echo "2. Testing Login Mutation (the problematic query)...\n";
$loginMutation = json_encode([
    'query' => 'mutation { login(email: "test@example.com", password: "password123") { id name email role } }'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginMutation);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Response: $response\n\n";

// Analyze the response
$responseData = json_decode($response, true);
if (isset($responseData['errors'])) {
    echo "Analysis:\n";
    foreach ($responseData['errors'] as $error) {
        if (strpos($error['message'], 'CSRF') !== false) {
            echo "❌ CSRF issue still exists\n";
        } elseif (strpos($error['message'], 'Unauthenticated') !== false) {
            echo "✅ CSRF issue resolved - now showing authentication error (expected)\n";
        } elseif (strpos($error['message'], 'user not found') !== false || strpos($error['message'], 'credentials') !== false) {
            echo "✅ CSRF issue resolved - now showing credential error (expected)\n";
        } else {
            echo "ℹ️  Other error: " . $error['message'] . "\n";
        }
    }
} elseif (isset($responseData['data'])) {
    echo "✅ Query executed successfully!\n";
} else {
    echo "❓ Unexpected response format\n";
}

echo "\n=== Test Complete ===\n";
