<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== GraphQL Endpoint Comprehensive Testing ===\n\n";

// Test function to make GraphQL requests
function testGraphQL($query, $description) {
    global $kernel;
    
    echo "Testing: $description\n";
    echo str_repeat('-', 50) . "\n";
    
    try {
        $request = \Illuminate\Http\Request::create('/graphql', 'POST');
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('Accept', 'application/json');
        
        // Set the request content
        $content = json_encode(['query' => $query]);
        $request->initialize([], [], [], [], [], [], $content);
        
        $response = $kernel->handle($request);
        
        echo "Status Code: " . $response->getStatusCode() . "\n";
        echo "Response:\n";
        
        $responseData = json_decode($response->getContent(), true);
        echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
    echo "\n" . str_repeat('=', 70) . "\n\n";
}

// Test 1: Schema Introspection
testGraphQL('{ __schema { queryType { name } } }', 'Schema Introspection');

// Test 2: Available Queries
testGraphQL('{ __schema { queryType { fields { name description } } } }', 'Available Queries');

// Test 3: Available Mutations
testGraphQL('{ __schema { mutationType { fields { name description } } } }', 'Available Mutations');

// Test 4: Available Types
testGraphQL('{ __type(name: "User") { name fields { name type { name } } } }', 'User Type Definition');

// Test 5: Available Types
testGraphQL('{ __type(name: "Route") { name fields { name type { name } } } }', 'Route Type Definition');

// Test 6: Test Route Query (should work if we have data)
testGraphQL('{ route(id: 1) { id route_name origin destination } }', 'Route Query');

// Test 7: Test Schedule Query (should work if we have data)
testGraphQL('{ schedule(id: 1) { id departure_time arrival_time price } }', 'Schedule Query');

// Test 8: Test User Query (might require authentication)
testGraphQL('{ user(id: 1) { id name email } }', 'User Query');

echo "=== Testing Complete ===\n";
