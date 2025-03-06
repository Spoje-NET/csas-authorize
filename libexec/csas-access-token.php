<?php

\define('APP_NAME', 'CsasTokenProvider');

require_once '../vendor/autoload.php';

/**
 * Get today's Statements list.
 */
$options = getopt('o::e::t:', ['output::', 'environment::', 'tokenId:']);
\Ease\Shared::init(
    ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'],
    \array_key_exists('environment', $options) ? $options['environment'] : (\array_key_exists('e', $options) ? $options['e'] : '../.env'),
);
$envFile = \array_key_exists('o', $options) ? $options['o'] : (\array_key_exists('output', $options) ? $options['output'] : \Ease\Shared::cfg('RESULT_FILE', 'php://stdout'));

function printUsage() {
    echo "Usage: php csas-access-token.php --tokenId=<TOKEN_ID> [--output=<OUTPUT_FILE>] [--environment=<ENVIRONMENT>]\n";
    echo "Options:\n";
    echo "  --tokenId, -t       The token ID (required)\n";
    echo "  --output, -o        The output file (optional)\n";
    echo "  --environment, -e   The environment file with DB_* fields (optional)\n";
    exit(0);
}

if (!\array_key_exists('tokenId', $options) && !\array_key_exists('t', $options)) {
    printUsage();
}

$tokenId = \array_key_exists('tokenId', $options) ? $options['tokenId'] : $options['t'];

// Fetch the token from the database
$token = new \SpojeNet\CSas\Token($tokenId, ['autoload' => true, 'keyColumn'=> (is_numeric($tokenId) ? 'id' : 'uuid' )]);

// Check if the access token is expired
$expiresAt = (new \DateTime())->setTimestamp($token->getDataValue('expires_in'));
$now = new \DateTime();

if ($expiresAt < $now) {
    // Refresh the token if it is expired
    $app = new \SpojeNet\CSas\Application($token->getDataValue('application_id'), ['autoload' => true]);
    $app->sandboxMode($token->getDataValue('environment') === 'sandbox');
    $token->refreshToken(new \SpojeNet\CSas\Auth($app));
}

// Write the required fields to the .env file
$envContent = sprintf(
    "ACCESS_TOKEN=%s\nSANDBOX_MODE=%s\n",
    $token->getDataValue('access_token'),
    $token->getDataValue('environment') === 'sandbox' ? 'true' : 'false'
);

if (!file_exists($envFile)) {
    file_put_contents($envFile, $envContent);
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
    $envData['ACCESS_TOKEN'] = $token->getDataValue('access_token');
    $envData['SANDBOX_MODE'] = $token->getDataValue('environment') === 'sandbox' ? 'true' : 'false';

    // Convert the associative array back to a string
    $updatedEnvContent = '';
    foreach ($envData as $key => $value) {
        $updatedEnvContent .= "$key=$value\n";
    }

    // Write the updated contents back to the .env file
    file_put_contents($envFile, $updatedEnvContent);
}
