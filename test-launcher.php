#!/usr/bin/env php
<?php

echo "Testing import-from-portal launcher\n";
echo "Arguments received: " . implode(' ', $argv) . "\n";

// Test the actual script path
$scriptPath = '/home/vitex/Projects/SpojeNetIT/csas-authorize/libexec/import-from-portal.php';
if (file_exists($scriptPath)) {
    echo "✅ Script file exists at: $scriptPath\n";
} else {
    echo "❌ Script file not found at: $scriptPath\n";
}

// Test if we can execute it
if (is_executable($scriptPath)) {
    echo "✅ Script is executable\n";
} else {
    echo "ℹ️  Script is not executable (but PHP can still run it)\n";
}

echo "Testing complete.\n";
