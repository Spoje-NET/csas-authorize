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

$tokenId = \Ease\WebPage::getRequestValue('id', 'int');

if (null === $tokenId) {
    header('Location: index.php');

    exit;
}

$token = new \SpojeNet\CSas\Token($tokenId, ['autoload' => true]);

$app = new \SpojeNet\CSas\Application($token->getDataValue('application_id'), ['autoload' => true]);

WebPage::singleton()->addItem(new PageTop(_('CSAS').': '._('Token')));

$tokenRow = new \Ease\TWB5\Row();
$opsCol = $tokenRow->addColumn(6, [
    new \Ease\Html\H2Tag($app->getDataValue('name')),
    new \Ease\Html\ImgTag($app->getDataValue('logo')),
]);

$tokenRow->addColumn(6, new TokenInfo($token));

WebPage::singleton()->container->addItem($tokenRow);

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
