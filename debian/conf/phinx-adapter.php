<?php

/**
 * csas-authorize Setup - Phinx database adapter.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2021-2024 Vitex Software
 */

require_once '/usr/share/csas-authorize/autoload.php';

if (file_exists('/etc/csas-authorize/csas-authorize.env')) {
    \Ease\Shared::instanced()->loadConfig('/etc/csas-authorize/csas-authorize.env', true);
}

$prefix = "/usr/lib/csas-authorize/db/";

$sqlOptions = [];

if (strstr(\Ease\Shared::cfg('DB_CONNECTION'), 'sqlite')) {
    $sqlOptions["database"] = "/var/lib/dbconfig-common/sqlite3/csas-authorize/" . basename(\Ease\Shared::cfg("DB_DATABASE"));
}

$engine = new \Ease\SQL\Engine(null, $sqlOptions);
$cfg = [
    'paths' => [
        'migrations' => [$prefix . 'migrations'],
        'seeds' => [$prefix . 'seeds']
    ],
    'environments' =>
    [
        'default_environment' => 'production',
        'production' => [
            'adapter' => \Ease\Shared::cfg('DB_CONNECTION'),
            'name' => $engine->database,
            'connection' => $engine->getPdo($sqlOptions)
        ],
    ]
];

return $cfg;
