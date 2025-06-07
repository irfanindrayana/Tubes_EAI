<?php

require_once 'vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Bootstrap Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CLEARING TRANSBANDUNG_TICKETING DATABASE ===\n";

try {
    $connection = 'ticketing';
    
    // Get all tables
    $tables = DB::connection($connection)->select('SHOW TABLES');
    $tableName = 'Tables_in_transbandung_ticketing';
    
    echo "Found " . count($tables) . " tables in database\n\n";
    
    // Disable foreign key checks
    DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=0');
    echo "✓ Disabled foreign key checks\n";
    
    $totalDeleted = 0;
    $processedTables = 0;
    
    foreach ($tables as $table) {
        $tableNameValue = $table->$tableName;
        
        // Skip migrations table
        if ($tableNameValue === 'migrations') {
            echo "⚠ Skipping migrations table (preserving migration history)\n";
            continue;
        }
        
        // Count rows before deletion
        $rowCount = DB::connection($connection)->table($tableNameValue)->count();
        echo "Processing {$tableNameValue}: {$rowCount} rows\n";
        
        if ($rowCount > 0) {
            DB::connection($connection)->table($tableNameValue)->truncate();
            $totalDeleted += $rowCount;
            echo "  ✓ Truncated {$rowCount} rows from {$tableNameValue}\n";
        } else {
            echo "  - Table {$tableNameValue} already empty\n";
        }
        
        $processedTables++;
    }
    
    // Re-enable foreign key checks
    DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=1');
    echo "✓ Re-enabled foreign key checks\n";
    
    echo "\n=== CLEANUP SUMMARY ===\n";
    echo "✅ SUCCESS!\n";
    echo "📊 Processed {$processedTables} tables\n";
    echo "🗑️ Deleted {$totalDeleted} total rows\n";
    echo "🏗️ Database structure preserved\n";
    echo "📋 Migration history preserved\n";
    
    // Final verification
    echo "\n=== VERIFICATION ===\n";
    $allEmpty = true;
    foreach ($tables as $table) {
        $tableNameValue = $table->$tableName;
        
        if ($tableNameValue === 'migrations') {
            continue;
        }
        
        $remainingRows = DB::connection($connection)->table($tableNameValue)->count();
        if ($remainingRows === 0) {
            echo "✓ {$tableNameValue}: Empty\n";
        } else {
            echo "⚠ {$tableNameValue}: {$remainingRows} rows remaining\n";
            $allEmpty = false;
        }
    }
    
    if ($allEmpty) {
        echo "\n🎉 TRANSBANDUNG_TICKETING DATABASE COMPLETELY CLEARED!\n";
        echo "The database is now ready for fresh data.\n";
    } else {
        echo "\n⚠ Some tables still contain data. Please check manually.\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
