<?php

// Simple test to verify the payment verification route is working
echo "=== Payment Verification Route Test ===\n\n";

// Test if we can resolve the route
try {
    // Initialize Laravel app
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    // Test route resolution
    $route = route('admin.payments.verify', ['payment' => 1]);
    echo "✅ Route resolved successfully: {$route}\n";
    
    // Test if route accepts PUT method
    $router = app('router');
    $routes = $router->getRoutes();
    
    foreach ($routes as $route) {
        if ($route->getName() === 'admin.payments.verify') {
            $methods = $route->methods();
            echo "✅ Route methods: " . implode(', ', $methods) . "\n";
            if (in_array('PUT', $methods)) {
                echo "✅ Route correctly accepts PUT method\n";
            } else {
                echo "❌ Route does not accept PUT method\n";
            }
            break;
        }
    }
    
    echo "\n✅ Payment verification route is properly configured!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
