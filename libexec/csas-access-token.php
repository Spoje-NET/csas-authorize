<?php

\define('APP_NAME', 'CsasTokenProvider');

require_once '../vendor/autoload.php';

/**
 * Get today's Statements list.
 */
$options = getopt('o::e::a::s::t:l', ['output::', 'environment::', 'tokenId::', 'list', 'accestokenKey::', 'sandboxmodeKey::']);
\Ease\Shared::init(
        ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'],
        \array_key_exists('environment', $options) ? $options['environment'] : (\array_key_exists('e', $options) ? $options['e'] : '../.env')
);
$envFile = \array_key_exists('o', $options) ? $options['o'] : (\array_key_exists('output', $options) ? $options['output'] : \Ease\Shared::cfg('RESULT_FILE', 'php://stdout'));

if (isset($options['list']) || isset($options['l'])) {
    // List available tokens
    $tokens = new \SpojeNet\CSas\Token();
    $tokenList = $tokens->listingQuery()->select('application.name')->leftJoin('application ON application.id = token.application_id');

    foreach ($tokenList as $tokenData) {
        echo sprintf("%s %s %s %s\n", $tokenData['environment'], $tokenData['uuid'], date('Y-m-d H:i:s', $tokenData['expires_in']), $tokenData['name']);
    }
    exit;
}

$tokenId = \array_key_exists('tokenId', $options) ? $options['tokenId'] : (\array_key_exists('t', $options) ? $options['t'] : null);

if (empty($tokenId)) {
    echo "Usage: php csas-access-token --tokenId=<TOKEN_ID> [--output=<OUTPUT_FILE>] [--environment=<ENVIRONMENT>] [--list]\n";
    echo "Options:\n";
    echo "  --tokenId, -t        The token ID (required)\n";
    echo "  --output, -o         The output file (optional)\n";
    echo "  --environment, -e    The environment file with DB_* fields (optional)\n";
    echo "  --list, -l           List available tokens (optional)\n";
    echo "  --accestokenKey, -a  Specify custom Access Token key instead of ACCESS_TOKEN\n";
    echo "  --sandboxmodeKey, -s Specify custom SandBox Mode key instead of SANDBOX_MODE\n";
    echo "\n";
    echo "Example:  csas-access-token -t71004963-e3d4-471f-96fc-1aef79d17ec1 -aCSAS_TOKEN -o.env\n";
} else {
    // Fetch the token from the database
    $token = new \SpojeNet\CSas\Token($tokenId, ['autoload' => true, 'keyColumn' => (is_numeric($tokenId) ? 'id' : 'uuid')]);

    $accesTokenKey = \array_key_exists('accestokenKey', $options) ? $options['accestokenKey'] : (array_key_exists('a', $options) ? $options['a'] : 'ACCESS_TOKEN');
    $sandboxModeKey = \array_key_exists('sandboxmodeKey', $options) ? $options['sandboxmodeKey'] : (array_key_exists('s', $options) ? $options['s'] : 'SANDBOX_MODE');

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
        $accesTokenKey . "=%s\n". $sandboxModeKey ."=%s\n",
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
        $envData[$accesTokenKey] = $token->getDataValue('access_token');
        $envData[$sandboxModeKey] = $token->getDataValue('environment') === 'sandbox' ? 'true' : 'false';

        // Convert the associative array back to a string
        $updatedEnvContent = '';
        foreach ($envData as $key => $value) {
            $updatedEnvContent .= "$key=$value\n";
        }

        // Write the updated contents back to the .env file
        file_put_contents($envFile, $updatedEnvContent);
    }
}
