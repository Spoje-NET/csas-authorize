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

/**
 * Zabbix agent helper: CSAS token expiration.
 *
 * Usage: csas-zabbix-token discovery|count|refresh_days <uuid>|access_seconds <uuid> [env-file]
 */
\define('APP_NAME', 'CsasZabbix');

$autoload = is_file('/usr/share/csas-authorize/autoload.php') ? '/usr/share/csas-authorize/autoload.php' : \dirname(__DIR__).'/vendor/autoload.php';
require_once $autoload;

$command = $argv[1] ?? '';
$uuid = $argv[2] ?? '';
$envFile = $argv[3] ?? (is_file('/etc/csas-authorize/csas-authorize.env') ? '/etc/csas-authorize/csas-authorize.env' : \dirname(__DIR__).'/.env');

\Ease\Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], $envFile);
\define('EASE_LOGGER', 'syslog');

$monitor = new \SpojeNet\CSas\ZabbixMonitor();

try {
    $tokens = $monitor->tokens();
} catch (\Throwable $exception) {
    fwrite(\STDERR, $exception->getMessage()."\n");

    exit(1);
}

switch ($command) {
    case 'discovery':
        echo \SpojeNet\CSas\ZabbixMonitor::discovery($tokens), "\n";

        break;
    case 'count':
        echo \count($tokens), "\n";

        break;
    case 'refresh_days':
    case 'access_seconds':
        foreach ($tokens as $row) {
            if ($row['uuid'] === $uuid) {
                echo $command === 'refresh_days' ? \SpojeNet\CSas\ZabbixMonitor::refreshDays($row) : \SpojeNet\CSas\ZabbixMonitor::accessSeconds($row), "\n";

                exit(0);
            }
        }

        fwrite(\STDERR, "Token {$uuid} not found\n");

        exit(2);
    default:
        fwrite(\STDERR, "Usage: csas-zabbix-token discovery|count|refresh_days <uuid>|access_seconds <uuid> [env-file]\n");

        exit(64);
}
