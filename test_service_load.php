<?php

require 'vendor/autoload.php';

echo "Testing InboxService load...\n";

try {
    $app = require 'bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    $kernel->bootstrap();
    
    $service = app('App\Services\InboxService');
    echo "SUCCESS: InboxService loaded without errors\n";
    
    // Test if methods exist
    if (method_exists($service, 'sendMessage')) {
        echo "SUCCESS: sendMessage method exists\n";
    } else {
        echo "ERROR: sendMessage method not found\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
