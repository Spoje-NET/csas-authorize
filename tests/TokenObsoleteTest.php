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
use SpojeNet\CSas\Token;

class TokenObsoleteTest extends TestCase
{
    private const NOW = 1_800_000_000;

    public function testOtherTokensAreFlaggedAndCurrentOneKept(): void
    {
        $rows = [
            ['id' => 1, 'refresh_token' => null, 'created_at' => self::NOW - 400 * 86400],
            ['id' => 2, 'refresh_token' => 'r', 'created_at' => self::NOW - 400 * 86400],
            ['id' => 3, 'refresh_token' => 'r', 'created_at' => self::NOW - 10 * 86400],
            ['id' => 4, 'refresh_token' => 'r', 'created_at' => self::NOW],
        ];

        $obsolete = Token::markObsolete($rows, 4, self::NOW);

        $this->assertSame([1, 2, 3], array_column($obsolete, 'id'));
        $this->assertSame([false, false, true], array_column($obsolete, 'usable'));
    }

    public function testNothingObsoleteForSingleToken(): void
    {
        $this->assertSame([], Token::markObsolete([['id' => 7, 'refresh_token' => 'r', 'created_at' => self::NOW]], 7, self::NOW));
    }
}
