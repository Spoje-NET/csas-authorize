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
