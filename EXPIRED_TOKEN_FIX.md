# Expired Refresh Token Error Handling - Fix Documentation

## Problem

The ČSAS OAuth2 client was experiencing fatal errors when refresh tokens expired. The error manifested as:

```
Fatal error: Uncaught League\OAuth2\Client\Provider\Exception\IdentityProviderException: request_error
```

With error details:
- **Error Code**: 7109
- **Error Description**: "Refresh token has expired"
- **HTTP Status**: 400 Bad Request

## Root Cause

The issue occurred because:

1. **Refresh tokens expire after 180 days** in the ČSAS system
2. The `Token::refreshToken()` method didn't handle `IdentityProviderException` properly
3. The `csas-access-token.php` script lacked exception handling for expired tokens
4. No graceful error handling or user feedback was provided

## Solution

### 1. Enhanced Token::refreshToken() Method

**File**: `src/SpojeNet/CSas/Token.php`

```php
public function refreshToken(AbstractProvider $provider): AccessToken
{
    $refreshToken = $this->getDataValue('refresh_token');

    if (empty($refreshToken)) {
        throw new \RuntimeException(_('No refresh token available'), 23);
    }

    try {
        $newToken = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $refreshToken,
        ]);

        $this->store($newToken);
        $expiresAt = (new \DateTime())->setTimestamp($this->getDataValue('expires_in'));
        $this->addStatusMessage(sprintf(_('Token Refreshed. Valid till: %s'), $expiresAt->format('Y-m-d H:i:s')), 'success');

        return $newToken;
    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $exception) {
        $errorData = $exception->getResponseBody();
        $errorMessage = $errorData['error_description'] ?? $exception->getMessage();
        $errorCode = $errorData['error_code'] ?? null;
        
        // Clear the expired refresh token
        $this->setDataValue('refresh_token', null);
        $this->dbSync();
        
        $this->addStatusMessage(sprintf(_('Token refresh failed: %s'), $errorMessage), 'error');
        
        // Check if this is specifically a refresh token expiration error
        if ($errorCode === '7109' || strpos($errorMessage, 'expired') !== false) {
            throw new \RuntimeException(_('Refresh token has expired'), 24, $exception);
        }
        
        // For other OAuth2 errors, throw a different exception
        throw new \RuntimeException(sprintf(_('OAuth2 error: %s'), $errorMessage), 25, $exception);
    }
}
```

**Key improvements:**
- Catches `IdentityProviderException`
- Detects ČSAS-specific error code `7109`
- Also detects "expired" keyword in error messages
- Clears expired refresh token from database
- Throws specific `RuntimeException` codes for different error types:
  - Code 23: No refresh token available
  - Code 24: Refresh token expired
  - Code 25: Other OAuth2 errors

### 2. Enhanced Web UI Error Handling

**File**: `src/token.php`

```php
if ($action === 'refresh') {
    try {
        $newToken = $token->refreshToken(new \SpojeNet\CSas\Auth($app));
    } catch (\RuntimeException $exception) {
        if ($exception->getCode() === 24) {
            // Refresh token has expired, redirect to re-authorization
            $token->addStatusMessage(_('Refresh token has expired. Please re-authorize the application.'), 'warning');
            WebPage::singleton()->redirect('auth.php?id=' . $app->getMyKey());
        } else {
            // Other runtime exception, re-throw it
            throw $exception;
        }
    }
}
```

**Key improvements:**
- Catches `RuntimeException` with code 24
- Provides user-friendly message
- Redirects to re-authorization flow automatically

### 3. Enhanced Command-Line Tool Error Handling

**File**: `libexec/csas-access-token.php`

```php
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
```

**Key improvements:**
- Provides JSON error response for `--json` flag
- Provides text error response for regular usage
- Exits with status code 1 for proper script automation
- Includes token UUID and application ID for identification

### 4. New Helper Methods

The following methods were already present in the `Token` class to help with proactive token management:

```php
/**
 * Check if refresh token is likely expired based on creation date.
 * Refresh tokens typically expire after 180 days.
 */
public function isRefreshTokenExpired(): bool
{
    $createdAt = $this->getDataValue('created_at');
    if (empty($createdAt)) {
        return true; // Assume expired if no creation date
    }
    
    $created = is_numeric($createdAt) 
        ? (new \DateTime())->setTimestamp($createdAt)
        : new \DateTime($createdAt);
        
    $expireDate = $created->modify('+180 days');
    
    return new \DateTime() > $expireDate;
}

/**
 * Check if token needs refresh (access token expired or about to expire).
 */
public function needsRefresh(): bool
{
    return $this->isExpired() || $this->tokenValiditySeconds() < 60; // Less than 1 minute remaining
}

/**
 * Get a human-readable status of the token.
 */
public function getTokenStatus(): array
{
    // Returns detailed status array with access_token and refresh_token status
}
```

## Exception Codes Reference

| Code | Description | Action Required |
|------|-------------|------------------|
| 23 | No refresh token available | Full re-authorization needed |
| 24 | Refresh token has expired | Re-authorization needed |
| 25 | Other OAuth2 error | Check error message, may need re-authorization |

## Usage Examples

### Web Application
```php
try {
    $newToken = $token->refreshToken($provider);
    // Token refreshed successfully
} catch (\RuntimeException $exception) {
    switch ($exception->getCode()) {
        case 24:
            // Redirect to auth.php
            header('Location: auth.php?id=' . $appId);
            break;
        case 23:
            // Show "authorize first" message
            break;
        case 25:
            // Handle other OAuth2 errors
            break;
    }
}
```

### Command Line Tool
```php
try {
    $token->refreshToken($provider);
} catch (\RuntimeException $exception) {
    if ($exception->getCode() === 24) {
        if ($jsonOutput) {
            echo json_encode(['error' => 'refresh_token_expired']);
        } else {
            echo "Error: Refresh token expired\n";
        }
        exit(1);
    }
    throw $exception;
}
```

## Prevention Strategies

1. **Monitor token expiration**:
   ```php
   $status = $token->getTokenStatus();
   if ($status['refresh_token']['status'] === 'expiring_soon') {
       // Notify user to re-authorize
   }
   ```

2. **Proactive checks**:
   ```php
   if ($token->isRefreshTokenExpired()) {
       // Redirect to authorization flow
   }
   ```

3. **Automated notifications**: Set up cron jobs to check for tokens expiring soon

## Testing

The fix includes comprehensive test coverage for:
- Expired token scenarios (error code 7109)
- Generic expired token messages
- RuntimeException code handling
- Error detection logic

Test files:
- `tests/src/SpojeNet/CSas/TokenExceptionHandlingTest.php`
- Existing token tests updated accordingly

## Conclusion

This fix provides:
- **Graceful error handling** instead of fatal errors
- **User-friendly messages** explaining what happened
- **Automatic redirect** to re-authorization in web UI
- **Proper JSON/text responses** in command-line tools
- **Comprehensive logging** of refresh attempts
- **Proactive monitoring** capabilities

The solution ensures that expired refresh tokens are handled gracefully across all interfaces (web UI, command-line tools, API integrations), providing a better user experience and preventing application crashes.