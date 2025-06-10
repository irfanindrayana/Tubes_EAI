<?php

echo "PHP Script Test\n";
echo "Current directory: " . getcwd() . "\n";
echo "Script file exists: " . (file_exists(__FILE__) ? "Yes" : "No") . "\n";

try {
    echo "Loading Laravel...\n";
    require_once __DIR__ . '/vendor/autoload.php';
    echo "Autoload loaded successfully\n";
    
    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "Laravel app bootstrapped\n";
    
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "Kernel created\n";
    
    // Test basic request
    $request = \Illuminate\Http\Request::create('/');
    echo "Test request created\n";
    
    echo "Basic Laravel test completed successfully\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
