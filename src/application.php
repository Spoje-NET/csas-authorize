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

$app = new \SpojeNet\CSas\Application(\Ease\WebPage::getRequestValue('id', 'int'),['autoload'=>true]);

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
$appRow->addColumn(6,new AppEditorForm($app));
$appRow->addColumn(6,new \Ease\Html\ImgTag($app->getDataValue('logo')));

WebPage::singleton()->container->addItem($appRow);

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
