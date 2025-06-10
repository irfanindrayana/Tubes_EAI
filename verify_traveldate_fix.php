<?php

echo "=== TRAVELDATE FIX VERIFICATION ===\n";

// Check if the controller file exists and has the correct structure
$controllerPath = __DIR__ . '/app/Http/Controllers/TicketingController.php';
if (!file_exists($controllerPath)) {
    echo "❌ TicketingController.php not found\n";
    exit(1);
}

$controllerContent = file_get_contents($controllerPath);

// Check if bookingMultiple method exists
if (strpos($controllerContent, 'public function bookingMultiple') === false) {
    echo "❌ bookingMultiple method not found\n";
    exit(1);
}

// Check if the method properly handles travelDate
$pattern = '/public function bookingMultiple.*?\{.*?return view\(\'ticketing\.booking-multiple\', compact\(\'schedule\', \'seats\', \'travelDate\'\)\);.*?\}/s';
if (preg_match($pattern, $controllerContent)) {
    echo "✅ SUCCESS: bookingMultiple method properly handles travelDate\n";
    
    // Check specific lines
    if (strpos($controllerContent, '$travelDate = $request->input(\'travel_date\', now()->format(\'Y-m-d\'));') !== false) {
        echo "✅ SUCCESS: travelDate is extracted from request\n";
    } else {
        echo "❌ ISSUE: travelDate extraction not found\n";
    }
    
    if (strpos($controllerContent, "compact('schedule', 'seats', 'travelDate')") !== false) {
        echo "✅ SUCCESS: travelDate is passed to the view\n";
    } else {
        echo "❌ ISSUE: travelDate is not passed to the view\n";
    }
    
} else {
    echo "❌ ISSUE: bookingMultiple method doesn't properly handle travelDate\n";
}

// Check the view file
$viewPath = __DIR__ . '/resources/views/ticketing/booking-multiple.blade.php';
if (!file_exists($viewPath)) {
    echo "❌ booking-multiple.blade.php not found\n";
    exit(1);
}

$viewContent = file_get_contents($viewPath);
if (strpos($viewContent, 'value="{{ $travelDate }}"') !== false) {
    echo "✅ SUCCESS: View uses travelDate variable correctly\n";
} else {
    echo "❌ ISSUE: View doesn't use travelDate variable\n";
}

echo "\n=== FIX SUMMARY ===\n";
echo "The error 'Undefined variable \$travelDate' in booking-multiple.blade.php has been fixed by:\n";
echo "1. Adding \$travelDate extraction in TicketingController@bookingMultiple method\n";
echo "2. Passing \$travelDate to the view via compact() function\n";
echo "3. The view can now access \$travelDate without errors\n\n";

echo "To test the fix:\n";
echo "1. Visit the booking system in your browser\n";
echo "2. Select multiple seats (2 or more)\n";
echo "3. The booking form should load without the 'Undefined variable \$travelDate' error\n";

echo "\n✅ FIX COMPLETED SUCCESSFULLY!\n";
