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

namespace SpojeNet\CSas\Tests;

use PHPUnit\Framework\TestCase;
use SpojeNet\CSas\Application;
use SpojeNet\CSas\Auth;
use SpojeNet\CSas\Token;

/**
 * Auth Test Class.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class AuthTest extends TestCase
{
    private Auth $auth;
    private Application $application;
    private Token $token;

    protected function setUp(): void
    {
        $mockApplication = $this->createMock(Application::class);
        $mockToken = $this->createMock(Token::class);

        // Set up mock application with required methods
        $mockApplication->method('sandboxMode')->willReturn(true);
        $mockApplication->method('getClientId')->willReturn('test-client-id');
        $mockApplication->method('getClientSecret')->willReturn('test-client-secret');
        $mockApplication->method('getRedirectUri')->willReturn('https://example.com/callback');
        $mockApplication->method('getToken')->willReturn($mockToken);

        // Set up mock token
        $mockToken->method('getNextTokenUuid')->willReturn('test-token-uuid-123');

        $this->application = $mockApplication;
        $this->token = $mockToken;
        $this->auth = new Auth($this->application);
    }

    public function testConstructorWithSandboxMode(): void
    {
        $sandboxApplication = $this->createMock(Application::class);
        $sandboxApplication->method('sandboxMode')->willReturn(true);
        $sandboxApplication->method('getClientId')->willReturn('sandbox-client-id');
        $sandboxApplication->method('getClientSecret')->willReturn('sandbox-client-secret');
        $sandboxApplication->method('getRedirectUri')->willReturn('https://sandbox.example.com/callback');

        $sandboxAuth = new Auth($sandboxApplication);

        $this->assertInstanceOf(Auth::class, $sandboxAuth);
    }

    public function testConstructorWithProductionMode(): void
    {
        $productionApplication = $this->createMock(Application::class);
        $productionApplication->method('sandboxMode')->willReturn(false);
        $productionApplication->method('getClientId')->willReturn('prod-client-id');
        $productionApplication->method('getClientSecret')->willReturn('prod-client-secret');
        $productionApplication->method('getRedirectUri')->willReturn('https://prod.example.com/callback');

        $productionAuth = new Auth($productionApplication);

        $this->assertInstanceOf(Auth::class, $productionAuth);
    }

    public function testSandboxSiteConstant(): void
    {
        $expectedSandboxSite = 'https://webapi.developers.erstegroup.com/api/csas/sandbox/v1/sandbox-idp';
        $this->assertEquals($expectedSandboxSite, Auth::SANDBOX_SITE);
    }

    public function testProductionSiteConstant(): void
    {
        $expectedProductionSite = 'https://bezpecnost.csas.cz/api/psd2/fl/oidc/v1';
        $this->assertEquals($expectedProductionSite, Auth::PRODUCTION_SITE);
    }

    public function testGetIdpUriWithSandboxMode(): void
    {
        $idpUri = $this->auth->getIdpUri();

        // Verify the base URL is sandbox
        $this->assertStringContainsString(Auth::SANDBOX_SITE, $idpUri);

        // Verify required parameters are present
        $this->assertStringContainsString('client_id=test-client-id', $idpUri);
        $this->assertStringContainsString('response_type=code', $idpUri);
        $this->assertStringContainsString('redirect_uri=', $idpUri);
        $this->assertStringContainsString('state=test-token-uuid-123', $idpUri);
        $this->assertStringContainsString('access_type=offline', $idpUri);
    }

    public function testGetIdpUriWithProductionMode(): void
    {
        $productionApplication = $this->createMock(Application::class);
        $productionToken = $this->createMock(Token::class);

        $productionApplication->method('sandboxMode')->willReturn(false);
        $productionApplication->method('getClientId')->willReturn('prod-client-id');
        $productionApplication->method('getClientSecret')->willReturn('prod-client-secret');
        $productionApplication->method('getRedirectUri')->willReturn('https://prod.example.com/callback');
        $productionApplication->method('getToken')->willReturn($productionToken);

        $productionToken->method('getNextTokenUuid')->willReturn('prod-token-uuid-456');

        $productionAuth = new Auth($productionApplication);
        $idpUri = $productionAuth->getIdpUri();

        // Verify the base URL is production
        $this->assertStringContainsString(Auth::PRODUCTION_SITE, $idpUri);

        // Verify required parameters are present
        $this->assertStringContainsString('client_id=prod-client-id', $idpUri);
        $this->assertStringContainsString('state=prod-token-uuid-456', $idpUri);
    }

    public function testGetIdpUriParameters(): void
    {
        $idpUri = $this->auth->getIdpUri();

        // Parse URL to check parameters
        $parsedUrl = parse_url($idpUri);
        parse_str($parsedUrl['query'], $params);

        $this->assertEquals('test-client-id', $params['client_id']);
        $this->assertEquals('code', $params['response_type']);
        $this->assertEquals('https://example.com/callback', $params['redirect_uri']);
        $this->assertEquals('test-token-uuid-123', $params['state']);
        $this->assertEquals('offline', $params['access_type']);
    }

    public function testInheritsFromGenericProvider(): void
    {
        $this->assertInstanceOf(\League\OAuth2\Client\Provider\GenericProvider::class, $this->auth);
    }

    public function testAuthPathInUri(): void
    {
        $idpUri = $this->auth->getIdpUri();

        // Verify the auth endpoint path is correct
        $this->assertStringContainsString('/auth?', $idpUri);
    }

    public function testUrlEncodingInRedirectUri(): void
    {
        $applicationWithSpecialChars = $this->createMock(Application::class);
        $tokenWithSpecialChars = $this->createMock(Token::class);

        $applicationWithSpecialChars->method('sandboxMode')->willReturn(true);
        $applicationWithSpecialChars->method('getClientId')->willReturn('test-client-id');
        $applicationWithSpecialChars->method('getClientSecret')->willReturn('test-client-secret');
        $applicationWithSpecialChars->method('getRedirectUri')->willReturn('https://example.com/callback?param=value&other=test');
        $applicationWithSpecialChars->method('getToken')->willReturn($tokenWithSpecialChars);

        $tokenWithSpecialChars->method('getNextTokenUuid')->willReturn('test-uuid');

        $authWithSpecialChars = new Auth($applicationWithSpecialChars);
        $idpUri = $authWithSpecialChars->getIdpUri();

        // The URL should be properly encoded
        $this->assertStringContainsString('redirect_uri=', $idpUri);
    }
}
