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
use SpojeNet\CSas\Token;

/**
 * Token Test Class
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class TokenTest extends TestCase
{
    private Token $token;
    private Application $application;

    protected function setUp(): void
    {
        $this->application = $this->createMock(Application::class);
        $this->application->method('getMyKey')->willReturn(1);
        
        // Create a partial mock that doesn't interact with the database
        $this->token = $this->getMockBuilder(Token::class)
            ->onlyMethods(['dbSync', 'addStatusMessage'])
            ->getMock();
            
        // Mock database operations to avoid constraint violations
        $this->token->expects($this->any())->method('dbSync')->willReturn(true);
        $this->token->expects($this->any())->method('addStatusMessage')->willReturn(null);
        
        $this->token->setApplication($this->application);
    }

    public function testRefreshTokenWithValidToken(): void
    {
        $mockProvider = $this->createMock(Auth::class);
        $mockAccessToken = $this->createMock(AccessToken::class);
        
        $mockAccessToken->method('getToken')->willReturn('new_access_token');
        $mockAccessToken->method('getRefreshToken')->willReturn('new_refresh_token');
        $mockAccessToken->method('getExpires')->willReturn(time() + 3600);
        
        $mockProvider->method('getAccessToken')->willReturn($mockAccessToken);
        
        $this->token->setDataValue('refresh_token', 'valid_refresh_token');
        
        $result = $this->token->refreshToken($mockProvider);
        
        $this->assertInstanceOf(AccessToken::class, $result);
    }

    public function testRefreshTokenWithExpiredToken(): void
    {
        $mockProvider = $this->createMock(Auth::class);
        
        $exception = new IdentityProviderException(
            'Refresh token has expired',
            400,
            ['error' => 'request_error', 'error_description' => 'Refresh token has expired']
        );
        
        $mockProvider->method('getAccessToken')->willThrowException($exception);
        
        $this->token->setDataValue('refresh_token', 'expired_refresh_token');
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Refresh token has expired');
        $this->expectExceptionCode(24);
        
        $this->token->refreshToken($mockProvider);
    }

    public function testRefreshTokenWithError7109(): void
    {
        $mockProvider = $this->createMock(Auth::class);
        
        $exception = new IdentityProviderException(
            'request_error',
            400,
            [
                'error' => 'request_error',
                'error_code' => '7109',
                'error_description' => 'Refresh token has expired'
            ]
        );
        
        $mockProvider->method('getAccessToken')->willThrowException($exception);
        
        $this->token->setDataValue('refresh_token', 'expired_refresh_token');
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Refresh token has expired');
        $this->expectExceptionCode(24);
        
        $this->token->refreshToken($mockProvider);
    }

    public function testRefreshTokenWithNoRefreshToken(): void
    {
        $mockProvider = $this->createMock(Auth::class);
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No refresh token available');
        
        $this->token->refreshToken($mockProvider);
    }

    public function testIsExpired(): void
    {
        // Test with expired token
        $this->token->setDataValue('expires_in', time() - 3600);
        $this->assertTrue($this->token->isExpired());
        
        // Test with valid token
        $this->token->setDataValue('expires_in', time() + 3600);
        $this->assertFalse($this->token->isExpired());
        
        // Test with null expires_in
        $this->token->setDataValue('expires_in', null);
        $this->assertFalse($this->token->isExpired());
    }

    public function testTokenValiditySeconds(): void
    {
        $futureTime = time() + 300;
        $this->token->setDataValue('expires_in', $futureTime);
        
        $validity = $this->token->tokenValiditySeconds();
        
        $this->assertGreaterThan(290, $validity);
        $this->assertLessThan(305, $validity);
    }

    public function testGetSandBoxMode(): void
    {
        $this->token->setDataValue('environment', 'sandbox');
        $this->assertTrue($this->token->getSandBoxMode());
        
        $this->token->setDataValue('environment', 'production');
        $this->assertFalse($this->token->getSandBoxMode());
    }

    public function testExportEnv(): void
    {
        $mockApp = $this->createMock(Application::class);
        $mockApp->method('getApiKey')->willReturn('test_api_key');
        
        $this->token->setApplication($mockApp);
        $this->token->setDataValue('uuid', 'test-uuid');
        $this->token->setDataValue('access_token', 'test_access_token');
        $this->token->setDataValue('environment', 'sandbox');
        
        $env = $this->token->exportEnv();
        
        $this->assertArrayHasKey('#CSAS_TOKEN_UUID', $env);
        $this->assertArrayHasKey('CSAS_API_KEY', $env);
        $this->assertArrayHasKey('CSAS_SANDBOX_MODE', $env);
        $this->assertArrayHasKey('CSAS_ACCESS_TOKEN', $env);
        
        $this->assertEquals('test-uuid', $env['#CSAS_TOKEN_UUID']);
        $this->assertEquals('test_api_key', $env['CSAS_API_KEY']);
        $this->assertEquals('true', $env['CSAS_SANDBOX_MODE']);
        $this->assertEquals('test_access_token', $env['CSAS_ACCESS_TOKEN']);
    }
}
