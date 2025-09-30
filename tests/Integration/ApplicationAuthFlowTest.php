<?php

declare(strict_types=1);

/**
 * This file is part of the CSASAuthorize  package
 *
 * https://github.com/Spoje-NET/csas-authorize
 *
 * (c) Spoje.Net IT s.r.o. <https://spojenet.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SpojeNet\CSas\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SpojeNet\CSas\Application;
use SpojeNet\CSas\Auth;
use SpojeNet\CSas\Token;

/**
 * Integration Test Class for Application-Auth-Token workflow.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class ApplicationAuthFlowTest extends TestCase
{
    public function testApplicationAuthTokenIntegration(): void
    {
        // Create a real Application instance
        $application = new Application();
        $application->setDataValue('name', 'Test Integration App');
        $application->setDataValue('production_client_id', 'test-client-id');
        $application->setDataValue('production_client_secret', 'test-client-secret');
        $application->setDataValue('production_redirect_uri', 'https://example.com/callback');
        $application->setDataValue('sandbox_client_id', 'sandbox-client-id');
        $application->setDataValue('sandbox_client_secret', 'sandbox-client-secret');
        $application->setDataValue('sandbox_redirect_uri', 'https://sandbox.example.com/callback');

        // Test production mode
        $application->sandboxMode(false);
        $this->assertFalse($application->sandboxMode());
        $this->assertEquals('test-client-id', $application->getClientId());
        $this->assertEquals('test-client-secret', $application->getClientSecret());
        $this->assertEquals('https://example.com/callback', $application->getRedirectUri());

        // Test sandbox mode
        $application->sandboxMode(true);
        $this->assertTrue($application->sandboxMode());
        $this->assertEquals('sandbox-client-id', $application->getClientId());
        $this->assertEquals('sandbox-client-secret', $application->getClientSecret());
        $this->assertEquals('https://sandbox.example.com/callback', $application->getRedirectUri());

        // Test Auth integration
        $auth = new Auth($application);
        $this->assertInstanceOf(Auth::class, $auth);

        // Test Token integration
        $token = $application->getToken();
        $this->assertInstanceOf(Token::class, $token);
    }

    public function testAuthUrlGeneration(): void
    {
        $application = new Application();
        $application->setDataValue('sandbox_client_id', 'test-client-123');
        $application->setDataValue('sandbox_redirect_uri', 'https://test.example.com/callback');
        $application->sandboxMode(true);

        $auth = new Auth($application);
        $idpUri = $auth->getIdpUri();

        // Verify the URL structure
        $this->assertIsString($idpUri);
        $this->assertStringContainsString('https://webapi.developers.erstegroup.com', $idpUri);
        $this->assertStringContainsString('client_id=test-client-123', $idpUri);
        $this->assertStringContainsString('response_type=code', $idpUri);
        $this->assertStringContainsString('access_type=offline', $idpUri);
    }

    public function testTokenStatusMethods(): void
    {
        $token = new Token();

        // Test with expired access token
        $token->setDataValue('expires_in', time() - 3600);
        $this->assertTrue($token->isExpired());
        $this->assertTrue($token->needsRefresh());

        // Test with valid access token
        $token->setDataValue('expires_in', time() + 3600);
        $this->assertFalse($token->isExpired());
        $this->assertFalse($token->needsRefresh());

        // Test with soon-expiring token (less than 60 seconds)
        $token->setDataValue('expires_in', time() + 30);
        $this->assertFalse($token->isExpired());
        $this->assertTrue($token->needsRefresh());
    }

    public function testTokenStatusInformation(): void
    {
        $token = new Token();
        $token->setDataValue('expires_in', time() + 300); // 5 minutes
        $token->setDataValue('created_at', (new \DateTime())->modify('-30 days')->format('Y-m-d H:i:s'));

        $status = $token->getTokenStatus();

        $this->assertIsArray($status);
        $this->assertArrayHasKey('access_token', $status);
        $this->assertArrayHasKey('refresh_token', $status);

        // Access token should be expiring soon (less than 5 minutes)
        $this->assertEquals('expiring_soon', $status['access_token']['status']);
        $this->assertEquals('warning', $status['access_token']['class']);

        // Refresh token should be valid (30 days old, expires after 180 days)
        $this->assertEquals('valid', $status['refresh_token']['status']);
        $this->assertEquals('success', $status['refresh_token']['class']);
    }

    public function testRefreshTokenExpiration(): void
    {
        $token = new Token();

        // Test with old refresh token (more than 180 days)
        $oldDate = (new \DateTime())->modify('-200 days')->format('Y-m-d H:i:s');
        $token->setDataValue('created_at', $oldDate);

        $this->assertTrue($token->isRefreshTokenExpired());

        // Test with recent refresh token
        $recentDate = (new \DateTime())->modify('-30 days')->format('Y-m-d H:i:s');
        $token->setDataValue('created_at', $recentDate);

        $this->assertFalse($token->isRefreshTokenExpired());
    }

    public function testApplicationImageUrl(): void
    {
        $uuid = 'test-app-uuid-123';
        $expectedUrl = 'https://webapi.developers.erstegroup.com/api/v1/file-manager/files2/test-app-uuid-123/image/small';

        $this->assertEquals($expectedUrl, Application::getImage($uuid));
    }

    public function testApplicationRedirectUriValidation(): void
    {
        $application = new Application();

        // Test with empty URIs
        $application->setDataValue('sandbox_redirect_uri', '');
        $application->setDataValue('production_redirect_uri', '');

        $this->assertFalse($application->hasSandboxRedirectUri());
        $this->assertFalse($application->hasProductionRedirectUri());

        // Test with valid URIs
        $application->setDataValue('sandbox_redirect_uri', 'https://sandbox.example.com/callback');
        $application->setDataValue('production_redirect_uri', 'https://prod.example.com/callback');

        $this->assertTrue($application->hasSandboxRedirectUri());
        $this->assertTrue($application->hasProductionRedirectUri());
    }
}
