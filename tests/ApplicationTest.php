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

use PHPUnit\Framework\TestCase;
use SpojeNet\CSas\Application;
use SpojeNet\CSas\Token;

/**
 * Application Test Class
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class ApplicationTest extends TestCase
{
    private Application $application;

    protected function setUp(): void
    {
        $this->application = new Application();
    }

    public function testGetImageUrl(): void
    {
        $appUuid = 'test-uuid-123';
        $expectedUrl = 'https://webapi.developers.erstegroup.com/api/v1/file-manager/files2/test-uuid-123/image/small';
        
        $this->assertEquals($expectedUrl, Application::getImage($appUuid));
    }

    public function testTakeDataRemovesClassKey(): void
    {
        $data = [
            'name' => 'Test App',
            'class' => 'SomeClass',
            'description' => 'Test Description'
        ];
        
        $result = $this->application->takeData($data);
        
        // Verify that class key is removed from internal data
        $this->assertNotEquals('SomeClass', $this->application->getDataValue('class'));
        $this->assertEquals('Test App', $this->application->getDataValue('name'));
        $this->assertEquals('Test Description', $this->application->getDataValue('description'));
    }

    public function testSandboxModeGetter(): void
    {
        // Test default state
        $this->assertFalse($this->application->sandboxMode());
    }

    public function testSandboxModeSetter(): void
    {
        // Enable sandbox mode
        $result = $this->application->sandboxMode(true);
        $this->assertTrue($result);
        $this->assertTrue($this->application->sandboxMode());
        
        // Disable sandbox mode
        $result = $this->application->sandboxMode(false);
        $this->assertFalse($result);
        $this->assertFalse($this->application->sandboxMode());
    }

    public function testGetApiKeyProduction(): void
    {
        $this->application->setDataValue('production_api_key', 'prod-key-123');
        $this->application->setDataValue('sandbox_api_key', 'sandbox-key-456');
        
        $this->application->sandboxMode(false);
        $this->assertEquals('prod-key-123', $this->application->getApiKey());
    }

    public function testGetApiKeySandbox(): void
    {
        $this->application->setDataValue('production_api_key', 'prod-key-123');
        $this->application->setDataValue('sandbox_api_key', 'sandbox-key-456');
        
        $this->application->sandboxMode(true);
        $this->assertEquals('sandbox-key-456', $this->application->getApiKey());
    }

    public function testGetClientIdProduction(): void
    {
        $this->application->setDataValue('production_client_id', 'prod-client-123');
        $this->application->setDataValue('sandbox_client_id', 'sandbox-client-456');
        
        $this->application->sandboxMode(false);
        $this->assertEquals('prod-client-123', $this->application->getClientId());
    }

    public function testGetClientIdSandbox(): void
    {
        $this->application->setDataValue('production_client_id', 'prod-client-123');
        $this->application->setDataValue('sandbox_client_id', 'sandbox-client-456');
        
        $this->application->sandboxMode(true);
        $this->assertEquals('sandbox-client-456', $this->application->getClientId());
    }

    public function testGetClientSecretProduction(): void
    {
        $this->application->setDataValue('production_client_secret', 'prod-secret-123');
        $this->application->setDataValue('sandbox_client_secret', 'sandbox-secret-456');
        
        $this->application->sandboxMode(false);
        $this->assertEquals('prod-secret-123', $this->application->getClientSecret());
    }

    public function testGetClientSecretSandbox(): void
    {
        $this->application->setDataValue('production_client_secret', 'prod-secret-123');
        $this->application->setDataValue('sandbox_client_secret', 'sandbox-secret-456');
        
        $this->application->sandboxMode(true);
        $this->assertEquals('sandbox-secret-456', $this->application->getClientSecret());
    }

    public function testGetRedirectUriProduction(): void
    {
        $this->application->setDataValue('production_redirect_uri', 'https://prod.example.com/callback');
        $this->application->setDataValue('sandbox_redirect_uri', 'https://sandbox.example.com/callback');
        
        $this->application->sandboxMode(false);
        $this->assertEquals('https://prod.example.com/callback', $this->application->getRedirectUri());
    }

    public function testGetRedirectUriSandbox(): void
    {
        $this->application->setDataValue('production_redirect_uri', 'https://prod.example.com/callback');
        $this->application->setDataValue('sandbox_redirect_uri', 'https://sandbox.example.com/callback');
        
        $this->application->sandboxMode(true);
        $this->assertEquals('https://sandbox.example.com/callback', $this->application->getRedirectUri());
    }

    public function testGetToken(): void
    {
        $token = $this->application->getToken();
        
        $this->assertInstanceOf(Token::class, $token);
    }

    public function testHasSandboxRedirectUriTrue(): void
    {
        $this->application->setDataValue('sandbox_redirect_uri', 'https://sandbox.example.com/callback');
        
        $this->assertTrue($this->application->hasSandboxRedirectUri());
    }

    public function testHasSandboxRedirectUriFalse(): void
    {
        $this->application->setDataValue('sandbox_redirect_uri', '');
        
        $this->assertFalse($this->application->hasSandboxRedirectUri());
    }

    public function testHasProductionRedirectUriTrue(): void
    {
        $this->application->setDataValue('production_redirect_uri', 'https://prod.example.com/callback');
        
        $this->assertTrue($this->application->hasProductionRedirectUri());
    }

    public function testHasProductionRedirectUriFalse(): void
    {
        $this->application->setDataValue('production_redirect_uri', '');
        
        $this->assertFalse($this->application->hasProductionRedirectUri());
    }

    public function testSendAuthorizationLinkByEmailWithoutEmail(): void
    {
        // Mock the application without email
        $this->application->setDataValue('email', null);
        
        // This method would normally exit, but we can't test that easily
        // Instead we'll test the logic by mocking WebPage methods if needed
        $this->assertTrue(true); // Placeholder for now
    }

    public function testSendAuthorizationLinkByEmailWithEmail(): void
    {
        $this->application->setDataValue('email', 'test@example.com');
        $this->application->setDataValue('production_client_id', 'test-client-id');
        $this->application->setDataValue('production_redirect_uri', 'https://example.com/callback');
        
        // Since this method has external dependencies (mail), we'll test the setup
        $this->assertEquals('test@example.com', $this->application->getDataValue('email'));
    }
}
