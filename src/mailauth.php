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

require_once '../vendor/autoload.php';

require_once './init.php';

$appId = WebPage::getRequestValue('app', 'int');

if ($appId) {
    $app = new \SpojeNet\CSas\Application($appId, ['autoload' => true]);
    $app->sandboxMode(WebPage::getRequestValue('env') === 'sandbox');

    if ($app->sendAuthorizationLinkByEmail()) {
        WebPage::singleton()->addStatusMessage(_('Authorization link has been sent to: ').$recipientEmail, 'success');
    } else {
        WebPage::singleton()->addStatusMessage(_('Failed to send authorization link to: ').$recipientEmail, 'error');
    }

    WebPage::singleton()->redirect('application.php?id='.$appId);
} else {
    header('Location: index.php');
}
