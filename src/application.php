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

$app = new \SpojeNet\CSas\Application(\Ease\WebPage::getRequestValue('id', 'int'), ['autoload' => true]);
// $app->pruneTokens();

$action = \Ease\WebPage::getRequestValue('action');

$instanceName = _($app->getDataValue('name') ?: _('n/a'));

switch ($action) {
    case 'delete':
        $app->deleteFromSQL();
        $app->addStatusMessage(sprintf(_('Application %s removal'), $app->getRecordName()), 'success');
        WebPage::singleton()->redirect('apps.php');

        break;

    default:
        if (WebPage::singleton()->isPosted()) {
            if ($app->takeData($_POST) && null !== $app->saveToSQL()) {
                $app->addStatusMessage(_('Application Saved'), 'success');
                //        $apps->prepareRemoteAbraFlexi();
                WebPage::singleton()->redirect('?id='.$app->getMyKey());
            } else {
                $app->addStatusMessage(_('Error saving Application'), 'error');
            }
        }

        break;
}

if (empty($instanceName) === false) {
    $instanceLink = '';
} else {
    $instanceName = _('New Application');
    $instanceLink = null;
}

WebPage::singleton()->addItem(new PageTop(_('CSAS').': '.$instanceName));

$appRow = new \Ease\TWB5\Row();
$opsCol = $appRow->addColumn(8, [
    new \Ease\Html\ImgTag($app->getDataValue('logo') ?: 'images/unknown.svg'),
    new AppTokenTable($app),
]);

$sandboxDisabled = !$app->hasSandboxRedirectUri() || empty($app->getDataValue('sandbox_client_id')) || empty($app->getDataValue('sandbox_client_secret')) || empty($app->getDataValue('sandbox_api_key'));
$productionDisabled = !$app->hasProductionRedirectUri() || empty($app->getDataValue('production_client_id')) || empty($app->getDataValue('production_client_secret')) || empty($app->getDataValue('production_api_key'));

if ($sandboxDisabled) {
    if (!$app->hasSandboxRedirectUri()) {
        $app->addStatusMessage(_('Sandbox redirect URI is not set. "Auth SandBox" button is disabled.'), 'warning');
    }

    if (empty($app->getDataValue('sandbox_client_id'))) {
        $app->addStatusMessage(_('Sandbox Client ID is not set. "Auth SandBox" button is disabled.'), 'warning');
    }

    if (empty($app->getDataValue('sandbox_client_secret'))) {
        $app->addStatusMessage(_('Sandbox Client Secret is not set. "Auth SandBox" button is disabled.'), 'warning');
    }

    if (empty($app->getDataValue('sandbox_api_key'))) {
        $app->addStatusMessage(_('Sandbox API Key is not set. "Auth SandBox" button is disabled.'), 'warning');
    }
}

if ($productionDisabled) {
    if (!$app->hasProductionRedirectUri()) {
        $app->addStatusMessage(_('Production redirect URI is not set. "Auth Production" button is disabled.'), 'warning');
    }

    if (empty($app->getDataValue('production_client_id'))) {
        $app->addStatusMessage(_('Production Client ID is not set. "Auth Production" button is disabled.'), 'warning');
    }

    if (empty($app->getDataValue('production_client_secret'))) {
        $app->addStatusMessage(_('Production Client Secret is not set. "Auth Production" button is disabled.'), 'warning');
    }

    if (empty($app->getDataValue('production_api_key'))) {
        $app->addStatusMessage(_('Production API Key is not set. "Auth Production" button is disabled.'), 'warning');
    }
}

$opsCol->addItem(new \Ease\TWB5\LinkButton(
    'auth.php?app='.$app->getMyKey().'&env=sandbox',
    '🧪 '._('Auth SandBox'),
    'primary'.($sandboxDisabled ? ' disabled' : ''),
));
$opsCol->addItem(new \Ease\TWB5\LinkButton(
    'auth.php?app='.$app->getMyKey().'&env=production',
    '🏭 '._('Auth Production'),
    'success'.($productionDisabled ? ' disabled' : ''),
));

// Add buttons for sending authorization link by email
$opsCol->addItem(new \Ease\TWB5\LinkButton(
    'mailauth.php?app='.$app->getMyKey().'&env=sandbox',
    '✉️ '._('Mail Auth Link SandBox'),
    'info'.($sandboxDisabled ? ' disabled' : ''),
));
$opsCol->addItem(new \Ease\TWB5\LinkButton(
    'mailauth.php?app='.$app->getMyKey().'&env=production',
    '✉️ '._('Mail Auth Link Production'),
    'info'.($productionDisabled ? ' disabled' : ''),
));

$appRow->addColumn(4, new AppEditorForm($app));

WebPage::singleton()->container->addItem($appRow);

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
