<?php

declare(strict_types=1);

/**
 * This file is part of the csas-authorize package
 *
 * https://csas-authorize.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

date_default_timezone_set('Europe/Prague');

require_once '../vendor/autoload.php';
\Ease\Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$daemonize = strtolower(\Ease\Shared::cfg('CSAS_AUTHORIZE_DAEMONIZE', 'true')) == 'true';
$loggers = ['syslog'];

if (strtolower(\Ease\Shared::cfg('APP_DEBUG', 'false')) === 'true') {
    $loggers[] = 'console';
}

\define('EASE_LOGGER', implode('|', $loggers));
\Ease\Shared::user(new \Ease\Anonym());

$token = new \SpojeNet\CSas\Token();
$token->logBanner( \Ease\Shared::appName().' '.\Ease\Shared::appVersion(), 'CSas authorize Daemon started');

do {
    $tokensCloseToExpire = $token->getTokensCloseToExpire();

    foreach ($tokensCloseToExpire as $tokenCloseToExpire) {
        $token->setData($tokenCloseToExpire);
        //Send notification with token renewal link
        $token->addStatusMessage('Token ' . $tokenCloseToExpire['uuid'] . ' is close to expire');

        $notificator = new \SpojeNet\CSas\Notificator($token);
        $notificator->send();
        
    }

    if ($daemonize) {
        sleep(\Ease\Shared::cfg('CSAS_AUTHORIZE_CYCLE_PAUSE', 3600));
    }
} while ($daemonize);

$token->logBanner('CSas authorize Daemon ended');
