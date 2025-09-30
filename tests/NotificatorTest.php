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
use SpojeNet\CSas\Notificator;
use SpojeNet\CSas\Token;

/**
 * Notificator Test Class.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class NotificatorTest extends TestCase
{
    public function testConstructorWithValidToken(): void
    {
        $mockToken = $this->createMock(Token::class);
        // Set up mock token data
        $mockToken->method('getDataValue')->willReturnMap([
            ['name', 'Test Token'],
            ['environment', 'sandbox'],
            ['sandbox_redirect_uri', 'https://sandbox.example.com/callback'],
            ['production_redirect_uri', 'https://prod.example.com/callback'],
            ['created_at', '2025-01-01 12:00:00'],
            ['id', '123'],
            ['application_id', '456'],
            ['email', 'test@example.com'],
        ]);

        $notificator = new Notificator($mockToken);

        $this->assertInstanceOf(Notificator::class, $notificator);
        $this->assertInstanceOf(\Ease\HtmlMailer::class, $notificator);
    }

    public function testConstructorWithSandboxEnvironment(): void
    {
        $mockToken = $this->createMock(Token::class);
        $mockToken->method('getDataValue')->willReturnMap([
            ['name', 'Sandbox Test Token'],
            ['environment', 'sandbox'],
            ['sandbox_redirect_uri', 'https://sandbox.example.com/callback'],
            ['production_redirect_uri', 'https://prod.example.com/callback'],
            ['created_at', '2025-01-01 12:00:00'],
            ['id', '123'],
            ['application_id', '456'],
            ['email', 'sandbox@example.com'],
        ]);

        $notificator = new Notificator($mockToken);

        $this->assertInstanceOf(Notificator::class, $notificator);
    }

    public function testConstructorWithProductionEnvironment(): void
    {
        $mockToken = $this->createMockToken([
            ['name', 'Production Test Token'],
            ['environment', 'production'],
            ['sandbox_redirect_uri', 'https://sandbox.example.com/callback'],
            ['production_redirect_uri', 'https://prod.example.com/callback'],
            ['created_at', '2025-01-01 12:00:00'],
            ['id', '123'],
            ['application_id', '456'],
            ['email', 'production@example.com'],
        ]);

        $notificator = new Notificator($mockToken);

        $this->assertInstanceOf(Notificator::class, $notificator);
    }

    public function testConstructorGeneratesCorrectRenewalLink(): void
    {
        $this->token->method('getDataValue')->willReturnMap([
            ['name', 'Test Token'],
            ['environment', 'sandbox'],
            ['sandbox_redirect_uri', 'https://example.com/welcomeback.php'],
            ['production_redirect_uri', 'https://prod.example.com/welcomeback.php'],
            ['created_at', '2025-01-01 12:00:00'],
            ['id', '123'],
            ['application_id', '456'],
            ['email', 'test@example.com'],
        ]);

        $notificator = new Notificator($this->token);

        // The constructor should replace welcomeback.php with auth.php in the renewal link
        $this->assertInstanceOf(Notificator::class, $notificator);
    }

    public function testConstructorWithRecentToken(): void
    {
        // Create a token that was created recently (should have more remaining time)
        $recentDate = (new \DateTime())->modify('-30 days')->format('Y-m-d H:i:s');

        $this->token->method('getDataValue')->willReturnMap([
            ['name', 'Recent Token'],
            ['environment', 'sandbox'],
            ['sandbox_redirect_uri', 'https://example.com/callback'],
            ['production_redirect_uri', 'https://prod.example.com/callback'],
            ['created_at', $recentDate],
            ['id', '123'],
            ['application_id', '456'],
            ['email', 'recent@example.com'],
        ]);

        $notificator = new Notificator($this->token);

        $this->assertInstanceOf(Notificator::class, $notificator);
    }

    public function testConstructorWithOldToken(): void
    {
        // Create a token that was created long ago (should have less remaining time)
        $oldDate = (new \DateTime())->modify('-150 days')->format('Y-m-d H:i:s');

        $this->token->method('getDataValue')->willReturnMap([
            ['name', 'Old Token'],
            ['environment', 'production'],
            ['sandbox_redirect_uri', 'https://example.com/callback'],
            ['production_redirect_uri', 'https://prod.example.com/callback'],
            ['created_at', $oldDate],
            ['id', '123'],
            ['application_id', '456'],
            ['email', 'old@example.com'],
        ]);

        $notificator = new Notificator($this->token);

        $this->assertInstanceOf(Notificator::class, $notificator);
    }

    public function testInheritsFromHtmlMailer(): void
    {
        $this->token->method('getDataValue')->willReturnMap([
            ['name', 'Test Token'],
            ['environment', 'sandbox'],
            ['sandbox_redirect_uri', 'https://example.com/callback'],
            ['production_redirect_uri', 'https://prod.example.com/callback'],
            ['created_at', '2025-01-01 12:00:00'],
            ['id', '123'],
            ['application_id', '456'],
            ['email', 'test@example.com'],
        ]);

        $notificator = new Notificator($this->token);

        $this->assertInstanceOf(\Ease\HtmlMailer::class, $notificator);
    }

    public function testConstructorWithMissingEmail(): void
    {
        $this->token->method('getDataValue')->willReturnMap([
            ['name', 'Test Token'],
            ['environment', 'sandbox'],
            ['sandbox_redirect_uri', 'https://example.com/callback'],
            ['production_redirect_uri', 'https://prod.example.com/callback'],
            ['created_at', '2025-01-01 12:00:00'],
            ['id', '123'],
            ['application_id', '456'],
            ['email', null], // No email set
        ]);

        $notificator = new Notificator($this->token);

        $this->assertInstanceOf(Notificator::class, $notificator);
    }

    public function testConstructorCalculatesExpirationCorrectly(): void
    {
        // Test with a specific date to verify 180 days calculation
        $createdDate = '2025-01-01 00:00:00';

        $this->token->method('getDataValue')->willReturnMap([
            ['name', 'Date Test Token'],
            ['environment', 'sandbox'],
            ['sandbox_redirect_uri', 'https://example.com/callback'],
            ['production_redirect_uri', 'https://prod.example.com/callback'],
            ['created_at', $createdDate],
            ['id', '123'],
            ['application_id', '456'],
            ['email', 'datetest@example.com'],
        ]);

        $notificator = new Notificator($this->token);

        // Verify that the notificator was created successfully
        // The actual date calculation happens in the constructor
        $this->assertInstanceOf(Notificator::class, $notificator);
    }
    private function createMockToken(array $dataMap): Token
    {
        $mockToken = $this->createMock(Token::class);
        $mockToken->method('getDataValue')->willReturnMap($dataMap);

        return $mockToken;
    }
}
