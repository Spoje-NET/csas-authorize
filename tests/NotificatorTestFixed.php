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
use SpojeNet\CSas\Notificator;
use SpojeNet\CSas\Token;

/**
 * Notificator Test Class
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class NotificatorTest extends TestCase
{
    public function testConstructorWithValidToken(): void
    {
        $mockToken = $this->createMock(Token::class);
        $mockToken->method('getDataValue')->willReturnMap([
            ['name', 'Test Token'],
            ['environment', 'sandbox'],
            ['sandbox_redirect_uri', 'https://sandbox.example.com/callback'],
            ['production_redirect_uri', 'https://prod.example.com/callback'],
            ['created_at', '2025-01-01 12:00:00'],
            ['id', '123'],
            ['application_id', '456'],
            ['email', 'test@example.com']
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
            ['email', 'sandbox@example.com']
        ]);

        $notificator = new Notificator($mockToken);
        
        $this->assertInstanceOf(Notificator::class, $notificator);
    }

    public function testConstructorWithProductionEnvironment(): void
    {
        $mockToken = $this->createMock(Token::class);
        $mockToken->method('getDataValue')->willReturnMap([
            ['name', 'Production Test Token'],
            ['environment', 'production'],
            ['sandbox_redirect_uri', 'https://sandbox.example.com/callback'],
            ['production_redirect_uri', 'https://prod.example.com/callback'],
            ['created_at', '2025-01-01 12:00:00'],
            ['id', '123'],
            ['application_id', '456'],
            ['email', 'production@example.com']
        ]);

        $notificator = new Notificator($mockToken);
        
        $this->assertInstanceOf(Notificator::class, $notificator);
    }

    public function testInheritsFromHtmlMailer(): void
    {
        $mockToken = $this->createMock(Token::class);
        $mockToken->method('getDataValue')->willReturnMap([
            ['name', 'Test Token'],
            ['environment', 'sandbox'],
            ['sandbox_redirect_uri', 'https://example.com/callback'],
            ['production_redirect_uri', 'https://prod.example.com/callback'],
            ['created_at', '2025-01-01 12:00:00'],
            ['id', '123'],
            ['application_id', '456'],
            ['email', 'test@example.com']
        ]);

        $notificator = new Notificator($mockToken);
        
        $this->assertInstanceOf(\Ease\HtmlMailer::class, $notificator);
    }
}
