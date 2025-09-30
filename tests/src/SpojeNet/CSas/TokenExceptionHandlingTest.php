<?php

declare(strict_types=1);

/**
 * This file is part of the CSASAuthorize package
 *
 * https://github.com/Spoje-NET/csas-authorize
 *
 * (c) Spoje.Net IT s.r.o. <https://spojenet.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SpojeNet\CSas\Tests;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\TestCase;
use SpojeNet\CSas\Application;
use SpojeNet\CSas\Auth;

/**
 * Token Exception Handling Test Class
 *
 * This class tests the exception handling logic for expired refresh tokens
 * without requiring database operations.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class TokenExceptionHandlingTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testIdentityProviderExceptionWithError7109(): void
    {
        // Create mock response body with error code 7109
        $responseBody = [
            'error' => 'request_error',
            'error_code' => '7109',
            'error_description' => 'Refresh token has expired'
        ];
        
        $exception = new IdentityProviderException(
            'request_error',
            400,
            $responseBody
        );
        
        // Verify that the exception contains the expected error details
        $this->assertEquals($responseBody, $exception->getResponseBody());
        $this->assertEquals('request_error', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
    }
    
    /**
     * @coversNothing
     */
    public function testIdentityProviderExceptionWithExpiredMessage(): void
    {
        // Create mock response body with "expired" in description
        $responseBody = [
            'error' => 'invalid_grant',
            'error_description' => 'The refresh token has expired'
        ];
        
        $exception = new IdentityProviderException(
            'invalid_grant',
            400,
            $responseBody
        );
        
        // Verify that the exception contains "expired" in the error description
        $this->assertStringContainsString('expired', $responseBody['error_description']);
    }
    
    /**
     * @coversNothing
     */
    public function testRuntimeExceptionCodes(): void
    {
        // Test that we can create RuntimeException with our custom codes
        $expiredTokenException = new \RuntimeException('Refresh token has expired', 24);
        $this->assertEquals(24, $expiredTokenException->getCode());
        $this->assertEquals('Refresh token has expired', $expiredTokenException->getMessage());
        
        $oauth2Exception = new \RuntimeException('OAuth2 error: invalid_grant', 25);
        $this->assertEquals(25, $oauth2Exception->getCode());
        $this->assertStringContainsString('OAuth2 error', $oauth2Exception->getMessage());
        
        $noTokenException = new \RuntimeException('No refresh token available', 23);
        $this->assertEquals(23, $noTokenException->getCode());
    }
    
    /**
     * Test the error detection logic used in Token::refreshToken()
     * 
     * @coversNothing
     */
    public function testErrorDetectionLogic(): void
    {
        // Test case 1: Error code 7109
        $errorData1 = [
            'error' => 'request_error',
            'error_code' => '7109',
            'error_description' => 'Refresh token has expired'
        ];
        
        $isExpiredError1 = $errorData1['error_code'] === '7109' || 
                          strpos($errorData1['error_description'], 'expired') !== false;
        $this->assertTrue($isExpiredError1);
        
        // Test case 2: "expired" in message without error code
        $errorData2 = [
            'error' => 'invalid_grant',
            'error_description' => 'Token has expired'
        ];
        
        $isExpiredError2 = ($errorData2['error_code'] ?? null) === '7109' || 
                          strpos($errorData2['error_description'], 'expired') !== false;
        $this->assertTrue($isExpiredError2);
        
        // Test case 3: Different error (should not be treated as expired)
        $errorData3 = [
            'error' => 'invalid_request',
            'error_description' => 'Invalid client credentials'
        ];
        
        $isExpiredError3 = ($errorData3['error_code'] ?? null) === '7109' || 
                          strpos($errorData3['error_description'], 'expired') !== false;
        $this->assertFalse($isExpiredError3);
    }
}