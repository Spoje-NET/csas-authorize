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

require_once '../vendor/autoload.php';
session_start();
\Ease\Shared::init(
    ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'],
    '../.env',
);
\Ease\Locale::singleton(null, '../i18n', 'csas-authorize');

// Ease restores the messages kept over redirect only into Shared::$statusMessages,
// the status line reads the logger: put them back there without logging them again
$restoredMessages = array_filter(\Ease\Shared::$statusMessages, static fn ($message): bool => $message instanceof \Ease\Logger\Message);

if ($restoredMessages) {
    (function (array $messages): void {
        array_push($this->messages, ...array_values($messages));
    })->call(\Ease\Shared::logger(), $restoredMessages);
    \Ease\Shared::$statusMessages = [];
}

/**
 * @global WebPage $oPage
 */
$oPage = new Ui\WebPage('');
Ui\WebPage::singleton($oPage);

date_default_timezone_set('Europe/Prague');

$script_tz = date_default_timezone_get();

if (strcmp($script_tz, \ini_get('date.timezone'))) {
    //    echo 'Script timezone differs from ini-set timezone.';
}
//    echo 'Script timezone and ini-set timezone match.';
