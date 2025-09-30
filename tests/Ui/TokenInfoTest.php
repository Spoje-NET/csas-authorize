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

namespace SpojeNet\CSas\Tests\Ui;

use PHPUnit\Framework\TestCase;
use SpojeNet\CSas\Token;
use SpojeNet\CSas\Ui\TokenInfo;

/**
 * TokenInfo Test Class.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class TokenInfoTest extends TestCase
{
    public function testConstructorWithValidToken(): void
    {
        $mockToken = $this->createMock(Token::class);
        $mockToken->method('getDataValue')->willReturnMap([
            ['uuid', 'test-uuid-123'],
            ['environment', 'sandbox'],
            ['expires_in', time() + 3600], // 1 hour from now
            ['created_at', '2025-01-01 12:00:00'],
        ]);

        $tokenInfo = new TokenInfo($mockToken);

        $this->assertInstanceOf(TokenInfo::class, $tokenInfo);
        $this->assertInstanceOf(\Ease\Html\DivTag::class, $tokenInfo);
    }

    public function testConstructorWithExpiredToken(): void
    {
        $mockToken = $this->createMock(Token::class);
        $mockToken->method('getDataValue')->willReturnMap([
            ['uuid', 'expired-uuid-456'],
            ['environment', 'production'],
            ['expires_in', time() - 3600], // 1 hour ago
            ['created_at', '2024-12-01 12:00:00'],
        ]);

        $tokenInfo = new TokenInfo($mockToken);

        $this->assertInstanceOf(TokenInfo::class, $tokenInfo);
    }

    public function testConstructorWithoutExpirationTime(): void
    {
        $mockToken = $this->createMock(Token::class);
        $mockToken->method('getDataValue')->willReturnMap([
            ['uuid', 'no-expiry-uuid-789'],
            ['environment', 'sandbox'],
            ['expires_in', null],
            ['created_at', '2025-01-01 12:00:00'],
        ]);

        $tokenInfo = new TokenInfo($mockToken);

        $this->assertInstanceOf(TokenInfo::class, $tokenInfo);
    }

    public function testConstructorWithSoonExpiringToken(): void
    {
        $mockToken = $this->createMock(Token::class);
        $mockToken->method('getDataValue')->willReturnMap([
            ['uuid', 'soon-expiring-uuid'],
            ['environment', 'sandbox'],
            ['expires_in', time() + 300], // 5 minutes from now
            ['created_at', '2025-01-01 12:00:00'],
        ]);

        $tokenInfo = new TokenInfo($mockToken);

        $this->assertInstanceOf(TokenInfo::class, $tokenInfo);
    }

    public function testConstructorWithLongLivedToken(): void
    {
        $mockToken = $this->createMock(Token::class);
        $mockToken->method('getDataValue')->willReturnMap([
            ['uuid', 'long-lived-uuid'],
            ['environment', 'production'],
            ['expires_in', time() + (180 * 24 * 3600)], // 180 days from now
            ['created_at', '2025-01-01 12:00:00'],
        ]);

        $tokenInfo = new TokenInfo($mockToken);

        $this->assertInstanceOf(TokenInfo::class, $tokenInfo);
    }

    public function testInheritsFromDivTag(): void
    {
        $mockToken = $this->createMock(Token::class);
        $mockToken->method('getDataValue')->willReturnMap([
            ['uuid', 'test-uuid'],
            ['environment', 'sandbox'],
            ['expires_in', time() + 3600],
            ['created_at', '2025-01-01 12:00:00'],
        ]);

        $tokenInfo = new TokenInfo($mockToken);

        $this->assertInstanceOf(\Ease\Html\DivTag::class, $tokenInfo);
    }

    public function testConstructorWithDifferentEnvironments(): void
    {
        // Test sandbox environment
        $sandboxToken = $this->createMock(Token::class);
        $sandboxToken->method('getDataValue')->willReturnMap([
            ['uuid', 'sandbox-uuid'],
            ['environment', 'sandbox'],
            ['expires_in', time() + 3600],
            ['created_at', '2025-01-01 12:00:00'],
        ]);

        $sandboxInfo = new TokenInfo($sandboxToken);
        $this->assertInstanceOf(TokenInfo::class, $sandboxInfo);

        // Test production environment
        $prodToken = $this->createMock(Token::class);
        $prodToken->method('getDataValue')->willReturnMap([
            ['uuid', 'prod-uuid'],
            ['environment', 'production'],
            ['expires_in', time() + 3600],
            ['created_at', '2025-01-01 12:00:00'],
        ]);

        $prodInfo = new TokenInfo($prodToken);
        $this->assertInstanceOf(TokenInfo::class, $prodInfo);
    }
}
