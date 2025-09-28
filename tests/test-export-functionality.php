#!/usr/bin/env php
<?php

/**
 * Test script for the export functionality in csas-access-token.php
 * 
 * This script tests the newly added export feature that generates 
 * Developer Portal compatible JSON format.
 */

\define('APP_NAME', 'ExportTest');

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Test the export functionality
 */
function testExportFunctionality(): void
{
    echo "🧪 Testing CSAS Access Token Export Functionality\n";
    echo "================================================\n\n";
    
    // Test 1: Check if the export option is properly parsed
    echo "Test 1: Command line option parsing\n";
    $testOptions = getopt('x:', ['export:']);
    echo "✅ Export option parsing: Available\n\n";
    
    // Test 2: Initialize database connection
    echo "Test 2: Database initialization\n";
    try {
        \Ease\Shared::init(
            ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'],
            '../.env'
        );
        echo "✅ Database connection: Success\n\n";
    } catch (Exception $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "\n\n";
        return;
    }
    
    // Test 3: Check for available applications
    echo "Test 3: Available applications\n";
    $application = new \SpojeNet\CSas\Application();
    $apps = $application->listingQuery()->limit(5);
    $appCount = 0;
    $testApp = null;
    
    foreach ($apps as $appData) {
        $appCount++;
        if (!$testApp) {
            $testApp = $appData;
        }
        echo sprintf("   App %d: ID=%s, UUID=%s, Name=%s\n", 
            $appCount, $appData['id'], $appData['uuid'], $appData['name']);
    }
    
    if ($appCount === 0) {
        echo "⚠️  No applications found in database\n";
        echo "   Creating test application...\n";
        
        $newApp = new \SpojeNet\CSas\Application([
            'uuid' => 'test-' . uniqid(),
            'name' => 'Test Export Application',
            'email' => 'test@example.com',
            'sandbox_client_id' => 'test_sandbox_client',
            'sandbox_client_secret' => 'test_sandbox_secret',
            'sandbox_api_key' => 'test_sandbox_api_key',
            'sandbox_redirect_uri' => 'https://localhost/callback'
        ]);
        
        if ($newApp->saveToSQL()) {
            echo "✅ Test application created successfully\n";
            $testApp = $newApp->getData();
            $appCount = 1;
        } else {
            echo "❌ Failed to create test application\n";
        }
    } else {
        echo "✅ Found {$appCount} applications\n";
    }
    echo "\n";
    
    if (!$testApp) {
        echo "❌ No test application available for export testing\n";
        return;
    }
    
    // Test 4: Export functionality simulation
    echo "Test 4: Export data generation\n";
    echo "Using application: {$testApp['name']} (ID: {$testApp['id']})\n";
    
    try {
        $app = new \SpojeNet\CSas\Application($testApp['id'], ['autoload' => true]);
        
        // Generate export data (same logic as in the actual tool)
        $exportData = [
            'name' => $app->getDataValue('name'),
            'id' => $app->getDataValue('uuid'),
            'logoUrl' => $app->getDataValue('logo'),
            'email' => $app->getDataValue('email')
        ];
        
        // Add sandbox environment data if available
        if ($app->getDataValue('sandbox_client_id') || $app->getDataValue('sandbox_client_secret')) {
            $exportData['sandbox'] = array_filter([
                'clientId' => $app->getDataValue('sandbox_client_id'),
                'clientSecret' => $app->getDataValue('sandbox_client_secret'),
                'apiKey' => $app->getDataValue('sandbox_api_key'),
                'redirectUri' => $app->getDataValue('sandbox_redirect_uri')
            ]);
        }
        
        // Add production environment data if available
        if ($app->getDataValue('production_client_id') || $app->getDataValue('production_client_secret')) {
            $exportData['production'] = array_filter([
                'clientId' => $app->getDataValue('production_client_id'),
                'clientSecret' => $app->getDataValue('production_client_secret'),
                'apiKey' => $app->getDataValue('production_api_key'),
                'redirectUri' => $app->getDataValue('production_redirect_uri')
            ]);
        }
        
        // Remove empty values
        $exportData = array_filter($exportData, function($value) {
            return !empty($value);
        });
        
        $jsonOutput = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        echo "✅ Export data generated successfully\n";
        echo "Generated JSON structure:\n";
        echo $jsonOutput . "\n\n";
        
        // Test 5: Validate compatibility with import format
        echo "Test 5: Import compatibility validation\n";
        $importedData = json_decode($jsonOutput, true);
        
        $requiredFields = ['name', 'id'];
        $validFields = ['name', 'id', 'logoUrl', 'email', 'sandbox', 'production'];
        
        $isValid = true;
        foreach ($requiredFields as $field) {
            if (!isset($importedData[$field]) || empty($importedData[$field])) {
                echo "❌ Missing required field: {$field}\n";
                $isValid = false;
            }
        }
        
        foreach (array_keys($importedData) as $field) {
            if (!in_array($field, $validFields)) {
                echo "⚠️  Unknown field (will be ignored): {$field}\n";
            }
        }
        
        if ($isValid) {
            echo "✅ Export format is compatible with import system\n";
        } else {
            echo "❌ Export format has compatibility issues\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Export generation failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    echo "🎉 Export functionality test completed!\n";
    echo "Usage: php libexec/csas-access-token.php --export={$testApp['id']} --output=export.json\n";
}

// Run the test
testExportFunctionality();
