<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== GraphQL Endpoint Testing ===\n\n";

// Test 1: Basic introspection query
echo "1. Testing GraphQL Introspection Query...\n";
try {
    $request = Request::create('/graphql', 'POST', [], [], [], [], json_encode([
        'query' => '{ __schema { queryType { name } } }'
    ]));
    $request->headers->set('Content-Type', 'application/json');
    
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Response: " . $response->getContent() . "\n\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 2: Get all available queries
echo "2. Testing Available Queries...\n";
try {
    $request = Request::create('/graphql', 'POST', [], [], [], [], json_encode([
        'query' => '{ __schema { queryType { fields { name description } } } }'
    ]));
    $request->headers->set('Content-Type', 'application/json');
    
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Response: " . $response->getContent() . "\n\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 3: Get all available mutations
echo "3. Testing Available Mutations...\n";
try {
    $request = Request::create('/graphql', 'POST', [], [], [], [], json_encode([
        'query' => '{ __schema { mutationType { fields { name description } } } }'
    ]));
    $request->headers->set('Content-Type', 'application/json');
    
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Response: " . $response->getContent() . "\n\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 4: Test user query (should require authentication)
echo "4. Testing User Query (unauthenticated)...\n";
try {
    $request = Request::create('/graphql', 'POST', [], [], [], [], json_encode([
        'query' => '{ user(id: 1) { id name email } }'
    ]));
    $request->headers->set('Content-Type', 'application/json');
    
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Response: " . $response->getContent() . "\n\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 5: Test route query
echo "5. Testing Route Query...\n";
try {
    $request = Request::create('/graphql', 'POST', [], [], [], [], json_encode([
        'query' => '{ route(id: 1) { id route_name origin destination } }'
    ]));
    $request->headers->set('Content-Type', 'application/json');
    
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Response: " . $response->getContent() . "\n\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== GraphQL Testing Complete ===\n";
