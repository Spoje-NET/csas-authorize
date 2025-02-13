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

require_once './init.php';

// WebPage::singleton()->onlyForLogged();

WebPage::singleton()->addItem(new PageTop(_('CSAS')));

WebPage::singleton()->container->addItem('put content here');

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
