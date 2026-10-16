<?php

declare(strict_types=1);

/**
 * Demo: Handling Expired Refresh Tokens
 * 
 * This script demonstrates how to properly handle expired refresh tokens
 * in the ČSAS Authorize application.
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== ČSAS Authorize: Expired Token Handling Demo ===\n\n";

// Simulate the error scenario
echo "1. Error Scenario:\n";
echo "   - The refresh token has expired after 180 days\n";
echo "   - API returns error code 7109: 'Refresh token has expired'\n";
echo "   - League\\OAuth2\\Client throws IdentityProviderException\n\n";

echo "2. Updated Token::refreshToken() method handles:\n";
echo "   - Catches IdentityProviderException\n";
echo "   - Checks for error code 7109 or 'expired' in message\n";
echo "   - Clears the expired refresh_token from database\n";
echo "   - Throws RuntimeException with code 24 for expired tokens\n";
echo "   - Throws RuntimeException with code 25 for other OAuth2 errors\n\n";

echo "3. Applications handle expired tokens by:\n";
echo "   - Catching RuntimeException with code 24\n";
echo "   - Redirecting user to re-authorization flow\n";
echo "   - Displaying appropriate error message\n\n";

echo "4. Command-line tools (csas-access-token) handle by:\n";
echo "   - Catching RuntimeException with code 24\n";
echo "   - Returning JSON error for --json format\n";
echo "   - Displaying text error for regular format\n";
echo "   - Exiting with status code 1\n\n";

echo "5. New helper methods added:\n";
echo "   - isRefreshTokenExpired(): Check if refresh token likely expired (180 days)\n";
echo "   - needsRefresh(): Check if access token needs refresh\n";
echo "   - getTokenStatus(): Get human-readable status of both tokens\n\n";

echo "6. Example error response (JSON format):\n";
$errorResponse = [
    'error' => 'refresh_token_expired',
    'error_description' => 'Refresh token has expired. Please re-authorize the application.',
    'uuid' => 'example-token-uuid',
    'application_id' => '123'
];
echo json_encode($errorResponse, JSON_PRETTY_PRINT) . "\n\n";

echo "7. Prevention strategies:\n";
echo "   - Monitor token expiration with getTokenStatus()\n";
echo "   - Set up notifications for tokens expiring soon\n";
echo "   - Implement automatic re-authorization flow\n";
echo "   - Store token creation dates for expiration tracking\n\n";

echo "=== Demo Complete ===\n";

/**
 * Example of proper error handling in application code:
 */
function handleTokenRefresh($token, $provider): bool
{
    try {
        $newToken = $token->refreshToken($provider);
        echo "Token refreshed successfully\n";
        return true;
    } catch (\RuntimeException $exception) {
        switch ($exception->getCode()) {
            case 24:
                // Refresh token expired
                echo "Error: Refresh token has expired. Re-authorization required.\n";
                // Redirect to auth flow or show re-auth message
                return false;
            case 25:
                // Other OAuth2 error
                echo "OAuth2 Error: " . $exception->getMessage() . "\n";
                // Handle other OAuth2 errors appropriately
                return false;
            case 23:
                // No refresh token available
                echo "Error: No refresh token available. Authorization required.\n";
                return false;
            default:
                // Unexpected error
                echo "Unexpected error: " . $exception->getMessage() . "\n";
                throw $exception;
        }
    }
}

echo "\nExample usage patterns included in code comments.\n";