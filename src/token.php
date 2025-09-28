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

use Kint\Kint;

require_once '../vendor/autoload.php';

require_once './init.php';

$tokenId = \Ease\WebPage::getRequestValue('id', 'int');

if (null === $tokenId) {
    header('Location: index.php');

    exit;
}

$token = new \SpojeNet\CSas\Token($tokenId, ['autoload' => true]);
$app = $token->getApplication();

$action = \Ease\WebPage::getRequestValue('action');

if ($action === 'delete') {
    $token->deleteFromSQL();
    $token->addStatusMessage(sprintf(_('Token %s removal'), $token->getMyKey()), 'success');
    WebPage::singleton()->redirect('application.php?id='.$app->getMyKey());
}

if ($action === 'refresh') {
    try {
        $newToken = $token->refreshToken(new \SpojeNet\CSas\Auth($app));
    } catch (\RuntimeException $exception) {
        if ($exception->getCode() === 24) {
            // Refresh token has expired, redirect to re-authorization
            $token->addStatusMessage(_('Refresh token has expired. Please re-authorize the application.'), 'warning');
            WebPage::singleton()->redirect('auth.php?id=' . $app->getMyKey());
        } else {
            // Other runtime exception, re-throw it
            throw $exception;
        }
    }
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

$opsCol->addItem(new \Ease\TWB5\LinkButton('token.php?id='.$tokenId.'&action=refresh', '♻️ ️'._('Refresh'), $token->tokenValiditySeconds() < 0 ? 'success' : 'secondary'));

$tokenRow->addColumn(6, new TokenInfo($token));

WebPage::singleton()->container->addItem($tokenRow);

$envArray = $token->exportEnv();
$envContent = '';

foreach ($envArray as $key => $value) {
    $envContent .= strtoupper($key).'='.$value."\n";
}

$envDiv = new \Ease\Html\DivTag(nl2br($envContent));

WebPage::singleton()->container->addItem($envDiv);

if ($action === 'test') {
    $apiInstance = new \SpojeNet\CSas\Accounts\DefaultApi(new \SpojeNet\CSas\ApiClient(
        [
            'apikey' => $envArray['CSAS_API_KEY'],
            'token' => $envArray['CSAS_ACCESS_TOKEN'],
            'debug' => false,
            'sandbox' => $envArray['CSAS_SANDBOX_MODE'],
        ],
    ));

    try {
        $toDate = new \DateTime();

        $result = $apiInstance->getAccounts();

        // Uložení výstupu Kint do proměnné

        ob_clean();
        Kint::$enabled_mode = true;
        Kint::$expanded = true; // Expand the dump by default
        ob_start();
        Kint::dump($result, true);
        $kintOutput = ob_get_clean();
    } catch (\Exception $e) {
        $kintOutput = 'Exception when calling DefaultApi->getAccounts: '.$e->getMessage();
    }
}

if (isset($kintOutput)) {
    WebPage::singleton()->container->addItem($kintOutput);
}

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
