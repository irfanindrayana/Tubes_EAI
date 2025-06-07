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
use Illuminate\Support\Facades\Schema;

echo "=== CLEAR TRANSBANDUNG_TICKETING DATABASE ===\n";
echo "WARNING: This will delete ALL data in the ticketing database!\n";
echo "Are you sure you want to continue? (type 'YES' to confirm): ";

$handle = fopen("php://stdin", "r");
$confirmation = trim(fgets($handle));
fclose($handle);

if ($confirmation !== 'YES') {
    echo "Operation cancelled.\n";
    exit(1);
}

echo "\nStarting database cleanup...\n";

try {
    // Connect to ticketing database
    $connection = 'ticketing';
    
    // Get all tables in the ticketing database
    $tables = DB::connection($connection)->select('SHOW TABLES');
    $tableName = 'Tables_in_transbandung_ticketing';
    
    echo "Found " . count($tables) . " tables in transbandung_ticketing database\n\n";
    
    // Disable foreign key checks
    DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=0');
    echo "✓ Disabled foreign key checks\n";
    
    $deletedTables = 0;
    $deletedRows = 0;
    
    foreach ($tables as $table) {
        $tableNameValue = $table->$tableName;
        
        // Skip migrations table to preserve migration history
        if ($tableNameValue === 'migrations') {
            echo "⚠ Skipping migrations table (preserving migration history)\n";
            continue;
        }
        
        echo "Processing table: {$tableNameValue}\n";
        
        // Count rows before deletion
        $rowCount = DB::connection($connection)->table($tableNameValue)->count();
        echo "  - Found {$rowCount} rows\n";
        
        // Delete all data
        if ($rowCount > 0) {
            DB::connection($connection)->table($tableNameValue)->truncate();
            echo "  ✓ Truncated {$rowCount} rows\n";
            $deletedRows += $rowCount;
        } else {
            echo "  - Table already empty\n";
        }
        
        $deletedTables++;
    }
    
    // Re-enable foreign key checks
    DB::connection($connection)->statement('SET FOREIGN_KEY_CHECKS=1');
    echo "✓ Re-enabled foreign key checks\n";
    
    echo "\n=== CLEANUP SUMMARY ===\n";
    echo "✓ Processed {$deletedTables} tables\n";
    echo "✓ Deleted {$deletedRows} total rows\n";
    echo "✓ Database structure preserved\n";
    echo "✓ Migration history preserved\n";
    
    // Verify cleanup
    echo "\n=== VERIFICATION ===\n";
    foreach ($tables as $table) {
        $tableNameValue = $table->$tableName;
        
        if ($tableNameValue === 'migrations') {
            continue;
        }
        
        $remainingRows = DB::connection($connection)->table($tableNameValue)->count();
        if ($remainingRows === 0) {
            echo "✓ {$tableNameValue}: 0 rows (clean)\n";
        } else {
            echo "⚠ {$tableNameValue}: {$remainingRows} rows remaining\n";
        }
    }
    
    echo "\n✅ TRANSBANDUNG_TICKETING DATABASE CLEARED SUCCESSFULLY!\n";
    echo "The database is now empty and ready for fresh data.\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Database cleanup failed!\n";
    exit(1);
}
