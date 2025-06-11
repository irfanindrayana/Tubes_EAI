<?php

// Test inbox functionality without Laravel bootstrap to avoid conflicts
echo "=== INBOX ERROR VERIFICATION TEST ===\n\n";

echo "1. Checking InboxService.php for duplicate sendMessage methods...\n";
$serviceFile = file_get_contents('app/Services/InboxService.php');
$methodCount = substr_count($serviceFile, 'public function sendMessage');
if ($methodCount > 1) {
    echo "❌ Found $methodCount sendMessage methods - duplication exists\n";
} else {
    echo "✅ Only 1 sendMessage method found - no duplication\n";
}

echo "\n2. Checking InboxController.php for unreadCount variable...\n";
$controllerFile = file_get_contents('app/Http/Controllers/InboxController.php');
if (strpos($controllerFile, '$unreadCount') !== false) {
    echo "✅ \$unreadCount variable found in controller\n";
} else {
    echo "❌ \$unreadCount variable not found in controller\n";
}

echo "\n3. Checking inbox view for safe property access...\n";
$viewFile = file_get_contents('resources/views/inbox/index.blade.php');
if (strpos($viewFile, 'isset($isRecipient->pivot)') !== false) {
    echo "✅ Safe pivot property access found in view\n";
} else {
    echo "❌ Safe pivot property access not found in view\n";
}

if (strpos($viewFile, '$message->id ?? $message[') !== false) {
    echo "✅ Safe ID access for routing found in view\n";
} else {
    echo "❌ Safe ID access for routing not found in view\n";
}

echo "\n4. Checking for proper Carbon date handling...\n";
if (strpos($viewFile, 'Carbon::parse') !== false) {
    echo "✅ Carbon date parsing found in view\n";
} else {
    echo "❌ Carbon date parsing not found in view\n";
}

echo "\n5. Checking route definitions...\n";
$routeOutput = shell_exec('php artisan route:list --name=inbox 2>&1');
if (strpos($routeOutput, 'inbox.show') !== false) {
    echo "✅ inbox.show route is defined\n";
} else {
    echo "❌ inbox.show route not found\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
echo "Check the Simple Browser for actual page rendering.\n";
