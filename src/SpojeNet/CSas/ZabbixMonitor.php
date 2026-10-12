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

namespace SpojeNet\CSas;

/**
 * Token expiration data for Zabbix (LLD + item values).
 *
 * @no-named-arguments
 */
class ZabbixMonitor
{
    /**
     * Validity of the refresh token in days.
     */
    public const REFRESH_TOKEN_DAYS = 180;

    /**
     * Value reported when there is no usable refresh token at all.
     */
    public const NO_REFRESH_TOKEN = -1;

    /**
     * All tokens with application name.
     *
     * @return array<int, array<string, mixed>>
     */
    public function tokens(): array
    {
        $token = new Token();

        return $token->listingQuery()
            ->select(['application.name'])
            ->leftJoin('application ON application.id = token.application_id')
            ->fetchAll();
    }

    /**
     * Zabbix low level discovery document.
     *
     * @param array<int, array<string, mixed>> $tokens
     */
    public static function discovery(array $tokens): string
    {
        $data = [];

        foreach ($tokens as $row) {
            $data[] = [
                '{#TOKEN_UUID}' => (string) $row['uuid'],
                '{#APP_NAME}' => (string) ($row['name'] ?? ''),
                '{#ENV}' => (string) ($row['environment'] ?? ''),
            ];
        }

        return json_encode(['data' => $data], \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
    }

    /**
     * Days left of the refresh token (negative when expired).
     *
     * Returns {@see self::NO_REFRESH_TOKEN} when the refresh token is missing
     * (it was cleared after a failed refresh) so the token needs reauthorization.
     *
     * @param array<string, mixed> $row  token table row
     * @param null|int             $now  current timestamp (for tests)
     */
    public static function refreshDays(array $row, ?int $now = null): int
    {
        if (empty($row['refresh_token'])) {
            return self::NO_REFRESH_TOKEN;
        }

        $createdAt = $row['created_at'] ?? null;

        if (empty($createdAt)) {
            return self::NO_REFRESH_TOKEN;
        }

        $created = is_numeric($createdAt) ? (int) $createdAt : (int) strtotime((string) $createdAt);
        $expiresAt = $created + self::REFRESH_TOKEN_DAYS * 86400;

        return (int) floor(($expiresAt - ($now ?? time())) / 86400);
    }

    /**
     * Seconds the access token stays valid (negative when expired).
     *
     * @param array<string, mixed> $row token table row
     */
    public static function accessSeconds(array $row, ?int $now = null): int
    {
        return (int) ($row['expires_in'] ?? 0) - ($now ?? time());
    }
}
