<?php

\define('APP_NAME', 'CsasTokenProvider');

require_once '../vendor/autoload.php';
require_once '../src/init.php';

use SpojeNet\CSas\DeveloperPortalImporter;

/**
 * CSAS Access Token and Application Management Tool.
 */
$options = getopt('o::e::a::s::t:l::j::x::ih', ['output::', 'environment::', 'tokenId::', 'list', 'accesTokenKey::', 'sandboxModeKey::', 'json', 'export::', 'import::', 'file::', 'example', 'help']);
\Ease\Shared::init(
    ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'],
    \array_key_exists('environment', $options) ? $options['environment'] : (\array_key_exists('e', $options) ? $options['e'] : '../.env')
);
$envFile = \array_key_exists('o', $options) ? $options['o'] : (\array_key_exists('output', $options) ? $options['output'] : \Ease\Shared::cfg('RESULT_FILE', 'php://stdout'));

if (isset($options['list']) || isset($options['l'])) {
    // List available tokens
    $tokens = new \SpojeNet\CSas\Token();
    $tokenList = $tokens->listingQuery()->select('application.name')->leftJoin('application ON application.id = token.application_id');

    if (isset($options['json']) || isset($options['j'])) {
        // Export token list in JSON format
        echo json_encode($tokenList->fetchAll(), JSON_PRETTY_PRINT);
    } else {
        // Export token list in plain text format
        foreach ($tokenList as $tokenData) {
            echo sprintf("%s %s %s %s\n", $tokenData['environment'], $tokenData['uuid'], date('Y-m-d H:i:s', $tokenData['expires_in']), $tokenData['name']);
        }
    }
    exit;
}

if (isset($options['export']) || isset($options['x'])) {
    // Export application data in Developer Portal import format
    $applicationId = \array_key_exists('export', $options) ? $options['export'] : (\array_key_exists('x', $options) ? $options['x'] : null);
    
    if (empty($applicationId)) {
        echo "Error: Application ID is required for export\n";
        echo "Usage: php csas-access-token --export=<APPLICATION_ID> [--output=<OUTPUT_FILE>]\n";
        exit(1);
    }
    
    $application = new \SpojeNet\CSas\Application($applicationId, ['autoload' => true, 'keyColumn' => (is_numeric($applicationId) ? 'id' : 'uuid')]);
    
    if (!$application->getDataValue('id')) {
        echo "Error: Application with ID '{$applicationId}' not found\n";
        exit(1);
    }
    
    // Generate export data in Developer Portal import format
    $exportData = [
        'name' => $application->getDataValue('name'),
        'id' => $application->getDataValue('uuid'),
        'logoUrl' => $application->getDataValue('logo'),
        'email' => $application->getDataValue('email')
    ];
    
    // Add sandbox environment data if available
    if ($application->getDataValue('sandbox_client_id') || $application->getDataValue('sandbox_client_secret')) {
        $exportData['sandbox'] = array_filter([
            'clientId' => $application->getDataValue('sandbox_client_id'),
            'clientSecret' => $application->getDataValue('sandbox_client_secret'),
            'apiKey' => $application->getDataValue('sandbox_api_key'),
            'redirectUri' => $application->getDataValue('sandbox_redirect_uri')
        ]);
    }
    
    // Add production environment data if available
    if ($application->getDataValue('production_client_id') || $application->getDataValue('production_client_secret')) {
        $exportData['production'] = array_filter([
            'clientId' => $application->getDataValue('production_client_id'),
            'clientSecret' => $application->getDataValue('production_client_secret'),
            'apiKey' => $application->getDataValue('production_api_key'),
            'redirectUri' => $application->getDataValue('production_redirect_uri')
        ]);
    }
    
    // Remove empty values
    $exportData = array_filter($exportData, function($value) {
        return !empty($value);
    });
    
    $jsonOutput = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
    if ($envFile === 'php://stdout') {
        echo $jsonOutput . "\n";
    } else {
        $written = file_put_contents($envFile, $jsonOutput);
        if ($written) {
            echo "✅ Success: Application '{$application->getDataValue('name')}' exported to {$envFile} ({$written} bytes)\n";
        } else {
            echo "❌ Error: Failed to write export data to {$envFile}\n";
            exit(1);
        }
    }
    exit;
}

if (isset($options['import']) || isset($options['i']) || isset($options['file']) || isset($options['example'])) {
    // Import application data from Developer Portal format
    
    if (isset($options['example'])) {
        echo "Example JSON format for import:\n";
        echo "===============================\n\n";
        echo DeveloperPortalImporter::getJsonExample() . "\n";
        exit;
    }
    
    $importData = null;
    $importSource = '';
    
    // Check for import from --import option (JSON string)
    if (isset($options['import']) || isset($options['i'])) {
        $jsonData = \array_key_exists('import', $options) ? $options['import'] : (\array_key_exists('i', $options) ? $options['i'] : null);
        if (!empty($jsonData)) {
            $importData = json_decode($jsonData, true);
            if ($importData === null) {
                echo "❌ Error: Invalid JSON format in --import parameter\n";
                exit(1);
            }
            $importSource = 'JSON string';
        }
    }
    
    // Check for import from file
    if (isset($options['file']) && empty($importData)) {
        $filePath = $options['file'];
        if (!file_exists($filePath)) {
            echo "❌ Error: File not found: {$filePath}\n";
            exit(1);
        }
        
        $jsonContent = file_get_contents($filePath);
        if ($jsonContent === false) {
            echo "❌ Error: Unable to read file: {$filePath}\n";
            exit(1);
        }
        
        $importData = json_decode($jsonContent, true);
        if ($importData === null) {
            echo "❌ Error: Invalid JSON format in file: {$filePath}\n";
            exit(1);
        }
        $importSource = "file: {$filePath}";
    }
    
    if (empty($importData)) {
        echo "❌ Error: No import data provided\n";
        echo "Usage: csas-access-token --import=<JSON_STRING> | --file=<JSON_FILE> | --example\n";
        exit(1);
    }
    
    // Perform the import
    try {
        $importer = new DeveloperPortalImporter();
        $success = $importer->importFromArray($importData);
        
        if ($success) {
            $app = $importer->getApplication();
            echo "✅ Success: Application imported successfully from {$importSource}\n";
            echo "   Database ID: " . $app->getMyKey() . "\n";
            echo "   Name: " . $app->getDataValue('name') . "\n";
            echo "   UUID: " . $app->getDataValue('uuid') . "\n";
            
            // Show environment info
            if ($app->getDataValue('sandbox_client_id')) {
                echo "   Sandbox: Configured\n";
            }
            if ($app->getDataValue('production_client_id')) {
                echo "   Production: Configured\n";
            }
        } else {
            echo "❌ Error: Import failed\n";
            exit(1);
        }
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        exit(1);
    }
    
    exit;
}

$tokenId = \array_key_exists('tokenId', $options) ? $options['tokenId'] : (\array_key_exists('t', $options) ? $options['t'] : null);

// Handle help option
if (isset($options['help']) || isset($options['h'])) {
    echo "CSAS Access Token and Application Management Tool\n";
    echo "===============================================\n\n";
    echo "Usage:\n";
    echo "  csas-access-token [options]\n\n";
    echo "Token Operations:\n";
    echo "  --tokenId, -t <ID>   The token ID (required for token operations)\n";
    echo "  --output, -o <FILE>  The output file (optional)\n";
    echo "  --environment, -e    The environment file with DB_* fields (optional)\n";
    echo "  --list, -l           List available tokens\n";
    echo "  --json, -j           Export token data in JSON format\n";
    echo "  --accesTokenKey, -a  Specify custom Access Token key instead of CSAS_ACCESS_TOKEN\n";
    echo "  --sandboxModeKey, -s Specify custom SandBox Mode key instead of CSAS_SANDBOX_MODE\n\n";
    echo "Application Operations:\n";
    echo "  --export, -x <ID>    Export application data in Developer Portal format\n";
    echo "  --import, -i <JSON>  Import application data from JSON string\n";
    echo "  --file <FILE>        Import application data from JSON file\n";
    echo "  --example            Show example JSON format for import\n\n";
    echo "General:\n";
    echo "  --help, -h           Show this help message\n\n";
    echo "Examples:\n";
    echo "  # Token operations\n";
    echo "  csas-access-token -t 71004963-e3d4-471f-96fc-1aef79d17ec1 -o .env\n";
    echo "  csas-access-token --list --json\n\n";
    echo "  # Application export/import\n";
    echo "  csas-access-token --export=1 --output=backup.json\n";
    echo "  csas-access-token --file=backup.json\n";
    echo "  csas-access-token --import='{\"name\":\"Test\", \"id\":\"uuid\", ...}'\n";
    echo "  csas-access-token --example\n\n";
    exit;
}

// Only proceed with token operations if we have a tokenId
if (!empty($tokenId)) {
    // Fetch the token from the database
    $token = new \SpojeNet\CSas\Token($tokenId, ['autoload' => true, 'keyColumn' => (is_numeric($tokenId) ? 'id' : 'uuid')]);

    if (\Ease\Shared::cfg('APP_DEBUG')) {
        $token->logBanner($token->getDataValue('uuid'), $envFile);
    }

    if ($token->getDataValue('environment')) {
        $accesTokenKey = \array_key_exists('accesTokenKey', $options) ? $options['accesTokenKey'] : (array_key_exists('a', $options) ? $options['a'] : 'CSAS_ACCESS_TOKEN');
        $sandboxModeKey = \array_key_exists('sandboxModeKey', $options) ? $options['sandboxModeKey'] : (array_key_exists('s', $options) ? $options['s'] : 'CSAS_SANDBOX_MODE');

        // Check if the access token is expired or will expire within 10 seconds
        $expiresAt = (new \DateTime())->setTimestamp($token->getDataValue('expires_in'));
        $now = new \DateTime();
        $expiresSoon = (clone $now)->modify('+10 seconds');

        if ($expiresAt < $expiresSoon) {
            // Refresh the token if it is expired or will expire soon
            try {
                $token->refreshToken(new \SpojeNet\CSas\Auth($token->getApplication()));
            } catch (\RuntimeException $exception) {
                if ($exception->getCode() === 24) {
                    // Refresh token has expired
                    if (isset($options['json']) || isset($options['j'])) {
                        echo json_encode([
                            'error' => 'refresh_token_expired',
                            'error_description' => _('Refresh token has expired. Please re-authorize the application.'),
                            'uuid' => $token->getDataValue('uuid'),
                            'application_id' => $token->getDataValue('application_id')
                        ], JSON_PRETTY_PRINT);
                    } else {
                        echo _('Error: Refresh token has expired. Please re-authorize the application.') . "\n";
                        echo 'Token UUID: ' . $token->getDataValue('uuid') . "\n";
                        echo 'Application ID: ' . $token->getDataValue('application_id') . "\n";
                    }
                    exit(1);
                } else {
                    // Other runtime exception, re-throw it
                    throw $exception;
                }
            }
        }

        if (isset($options['json']) || isset($options['j'])) {
            // Export token data in JSON format
            echo json_encode($token->getData(), JSON_PRETTY_PRINT);
        } else {
            // Export token data in .env format
            $envArray = $token->exportEnv();

            if (!file_exists($envFile)) {
                $envContent = '';

                foreach ($envArray as $key => $value) {
                    $envContent .= strtoupper($key) . '=' . $value . "\n";
                }
                $written = file_put_contents($envFile, $envContent);
                $token->addStatusMessage(sprintf(_('%s bytes of Token %s written to %s '), $written, $token->getDataValue('uuid'), $envFile), $written ? 'success' : 'error');
            } else {
                // Read the existing .env file
                $existingEnvContent = file_get_contents($envFile);
                $envLines = explode("\n", $existingEnvContent);
                $envData = [];

                // Parse the existing .env file into an associative array
                foreach ($envLines as $line) {
                    if (strpos($line, '=') !== false) {
                        list($key, $value) = explode('=', $line, 2);
                        $envData[trim($key)] = trim($value);
                    }
                }

                // Update the necessary fields
                $envData['#CSAS_TOKEN_UUID'] = $envArray['#CSAS_TOKEN_UUID'];

                if (array_key_exists('CSAS_API_KEY', $envData)) {
                    if ($envData['CSAS_API_KEY'] != $envArray['CSAS_API_KEY']) {
                        $token->addStatusMessage(sprintf(_('Used CSAS_API_KEY is different. Exporting new one %s'), $envArray['CSAS_API_KEY']), 'warning');
                        $envData['#OLD_CSAS_API_KEY'] = $envData['CSAS_API_KEY'];
                        $envData['CSAS_API_KEY'] = $envArray['CSAS_API_KEY'];
                    }
                } else {
                    $token->addStatusMessage(sprintf(_('CSAS_API_KEY missing in current configuration. Exporting new one %s'), $envArray['CSAS_API_KEY']));
                    $envData['CSAS_API_KEY'] = $envArray['CSAS_API_KEY'];
                }

                $envData[$accesTokenKey] = $envArray['CSAS_ACCESS_TOKEN'];
                $envData[$sandboxModeKey] = $envArray['CSAS_SANDBOX_MODE'];

                // Convert the associative array back to a string
                $updatedEnvContent = '';
                foreach ($envData as $key => $value) {
                    $updatedEnvContent .= "$key=$value\n";
                }

                // Write the updated contents back to the .env file
                $written = file_put_contents($envFile, $updatedEnvContent);
                $token->addStatusMessage(sprintf(_('%s bytes of Token %s written to %s '), $written, $token->getDataValue('uuid'), $envFile), $written ? 'success' : 'error');
            }
        }
    } else {
        $token->addStatusMessage(sprintf(_('Token %s not found'), $tokenId), 'warning');
        exit(1);
    }
} else {
    // No tokenId provided and no other operation was specified
    echo "❌ Error: No valid operation specified\n";
    echo "Usage: csas-access-token [--tokenId=<TOKEN_ID>] [--export=<APP_ID>] [--import=<JSON>] [--file=<FILE>] [options]\n";
    echo "Use --help for detailed usage information.\n";
    exit(1);
}
