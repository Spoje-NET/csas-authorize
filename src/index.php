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
WebPage::singleton()->addItem(new PageTop(_('CSAS')));

// Basic Auth check
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    WebPage::singleton()->container->addItem(
        new \Ease\TWB5\Alert(
            'danger',
            _('🚨 This page is not protected! At least Basic Auth is recommended.'),
        ),
    );
}

WebPage::singleton()->container->addItem(new \Ease\Html\ImgTag('images/csas-authorize.svg', _('CSAS Authorize'), ['align' => 'right']));
WebPage::singleton()->container->addItem(new \Ease\Html\H1Tag(_('CSAS Authorize')));

$buttonRow = new \Ease\TWB5\Row();
$buttonRow->addColumn(6, new \Ease\TWB5\LinkButton('application.php', _('New Application'), 'primary'));
$buttonRow->addColumn(6, new \Ease\TWB5\LinkButton('import.php', _('Import from Developer Portal'), 'success'));
WebPage::singleton()->container->addItem($buttonRow);

WebPage::singleton()->container->addItem(new \SpojeNet\CSas\Ui\AppTable(new \SpojeNet\CSas\Application()));

// WebPage::singleton()->container->addItem(new \Ease\TWB5\LinkButton('auth.php', _('Authorization'), 'primary'));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
