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

namespace SpojeNet\CSas\Ui;

use Ease\Shared as Shr;

require_once '../vendor/autoload.php';

date_default_timezone_set('Europe/Prague');

session_start();

Shr::Init(['CSAS_API_KEY', 'CSAS_CLIENT_ID', 'CSAS_CLIENT_SECRET', 'CSAS_REDIRECT_URI'], '../.env');

$auth = new Auth();

$idpUri = $auth->getIdpUri();

if (\PHP_SAPI === 'cli') {
    echo $idpUri;
} else {
    header('Location: '.$idpUri);
    echo '<a href='.$idpUri.'>'.$idpUri.'</a>';
}
