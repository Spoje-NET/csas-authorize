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

WebPage::singleton()->container->addItem(new \Ease\Html\ImgTag('images/csas-authorize.svg', _('CSAS Authorize'), ['align' => 'right']));
WebPage::singleton()->container->addItem(new \Ease\Html\H1Tag(_('CSAS Authorize')));
WebPage::singleton()->container->addItem(new \Ease\TWB5\LinkButton('application.php', _('Application'), 'primary'));

WebPage::singleton()->container->addItem(new \SpojeNet\CSas\Ui\AppTable(new \SpojeNet\CSas\Application()));

WebPage::singleton()->container->addItem(new \Ease\TWB5\LinkButton('auth.php', _('Authorization'), 'primary'));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
