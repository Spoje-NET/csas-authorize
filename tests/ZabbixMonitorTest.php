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
use SpojeNet\CSas\ZabbixMonitor;

class ZabbixMonitorTest extends TestCase
{
    private const NOW = 1_800_000_000;

    public function testDiscovery(): void
    {
        $json = ZabbixMonitor::discovery([
            ['uuid' => 'u-1', 'name' => 'ČSAS do Pohody', 'environment' => 'production'],
        ]);

        $this->assertSame(
            ['data' => [['{#TOKEN_UUID}' => 'u-1', '{#APP_NAME}' => 'ČSAS do Pohody', '{#ENV}' => 'production']]],
            json_decode($json, true),
        );
        $this->assertSame('{"data":[]}', ZabbixMonitor::discovery([]));
    }

    public function testRefreshDaysCountsFromCreation(): void
    {
        $row = ['refresh_token' => 'x', 'created_at' => self::NOW - 100 * 86400];
        $this->assertSame(80, ZabbixMonitor::refreshDays($row, self::NOW));
    }

    public function testRefreshDaysAcceptsSqlDatetime(): void
    {
        $row = ['refresh_token' => 'x', 'created_at' => gmdate('Y-m-d H:i:s', self::NOW - 179 * 86400 - 60)];
        $this->assertSame(0, ZabbixMonitor::refreshDays($row, self::NOW));
    }

    public function testRefreshDaysNegativeWhenExpired(): void
    {
        $row = ['refresh_token' => 'x', 'created_at' => self::NOW - 200 * 86400];
        $this->assertLessThan(0, ZabbixMonitor::refreshDays($row, self::NOW));
    }

    public function testMissingRefreshTokenReportsMinusOne(): void
    {
        $this->assertSame(-1, ZabbixMonitor::refreshDays(['refresh_token' => null, 'created_at' => self::NOW], self::NOW));
        $this->assertSame(-1, ZabbixMonitor::refreshDays(['refresh_token' => 'x'], self::NOW));
    }

    public function testAccessSeconds(): void
    {
        $this->assertSame(300, ZabbixMonitor::accessSeconds(['expires_in' => self::NOW + 300], self::NOW));
        $this->assertSame(-60, ZabbixMonitor::accessSeconds(['expires_in' => self::NOW - 60], self::NOW));
    }
}
