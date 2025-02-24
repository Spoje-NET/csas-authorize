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
$app->sandboxMode($token->getDataValue('environment') === 'sandbox');

$action = \Ease\WebPage::getRequestValue('action');

if ($action === 'delete') {
    $token->deleteFromSQL();
    $token->addStatusMessage(sprintf(_('Token %s removal'), $token->getMyKey()), 'success');
    WebPage::singleton()->redirect('application.php?id='.$app->getMyKey());
}

if ($action === 'refresh') {
    $newToken = $token->refreshToken(new \SpojeNet\CSas\Auth($app));
}

WebPage::singleton()->addItem(new PageTop(_('CSAS').': '._('Token')));

$tokenRow = new \Ease\TWB5\Row();
$opsCol = $tokenRow->addColumn(6, [
    new \Ease\Html\H2Tag($app->getDataValue('name')),
    new \Ease\Html\ImgTag($app->getDataValue('logo')),
    new \Ease\TWB5\LinkButton('token.php?id='.$tokenId.'&action=test', '💨 '._('Test'), 'warning'),
]);

if ($token->isExpired()) {
    $opsCol->addItem(new \Ease\TWB5\LinkButton('token.php?id='.$tokenId.'&action=delete', '🗑 ️'._('Remove'), 'danger'));
} else {
    $opsCol->addItem(new \Ease\TWB5\LinkButton('', '🗑 ️'._('Remove'), 'danger disabled'));
}

  
$opsCol->addItem(new \Ease\TWB5\LinkButton('token.php?id='.$tokenId.'&action=refresh', '♻️ ️'._('Refresh'), $token->tokenValiditySeconds() < 0 ? 'success' :  'secondary'));

$tokenRow->addColumn(6, new TokenInfo($token));

WebPage::singleton()->container->addItem($tokenRow);

if ($action === 'test') {
    $apiInstance = new \SpojeNET\Csas\Accounts\DefaultApi(new \SpojeNET\Csas\ApiClient(
        [
            'apikey' => $app->getApiKey(),
            'token' => $token->getDataValue('access_token'),
            'debug' => false,
            'sandbox' => $app->sandboxMode(),
        ],
    ));

    try {
        $toDate = new \DateTime();
        $fromDate = (clone $toDate)->modify('-1 month');
        # $result = $apiInstance->getStatements('AA195E7DB499B4D9F48D46C208625FF53F2245F7', $fromDate->format('Y-m-d'), $toDate->format('Y-m-d'));
        
        $result =  $apiInstance->getTransactions('AA195E7DB499B4D9F48D46C208625FF53F2245F7', $fromDate->format('Y-m-d'), $toDate->format('Y-m-d'));

        WebPage::singleton()->container->addItem(new \Ease\Html\PreTag(nl2br(print_r($result, true))));
    } catch (\Exception $e) {
        WebPage::singleton()->container->addItem('Exception when calling DefaultApi->getAccounts: ', $e->getMessage());
    }
}

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
