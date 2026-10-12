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

$state = WebPage::getRequestValue('state');
$code = WebPage::getRequestValue('code');
$token = new \SpojeNet\CSas\Token(null, ['keyColumn' => 'uuid']);
$token->loadFromSQL($state);

WebPage::singleton()->addItem(new PageTop(_('CSAS Authorize')));
WebPage::singleton()->container->addItem(new \Ease\Html\ImgTag('images/csas-authorize.svg', _('CSAS Authorize'), ['align' => 'right']));
WebPage::singleton()->container->addItem(new \Ease\Html\H1Tag(_('CSAS Authorize')));

if ($token->getMyKey()) {
    $app = new \SpojeNet\CSas\Application($token->getDataValue('application_id'), ['autoload' => true]);
    $app->sandboxMode($token->getDataValue('environment') === 'sandbox');

    $provider = new \SpojeNet\CSas\Auth($app);

    // Start session
    // If we don't have an authorization code then get one
    if (!isset($code)) {
        // Fetch the authorization URL from the provider; this returns the
        // urlAuthorize option and generates and applies any necessary parameters
        // (e.g. state).
        $authorizationUrl = $provider->getAuthorizationUrl();

        // Get the state generated for you and store it to the session.
        $_SESSION['oauth2state'] = $provider->getState();

        // Optional, only required when PKCE is enabled.
        // Get the PKCE code generated for you and store it to the session.
        $_SESSION['oauth2pkceCode'] = $provider->getPkceCode();

        // Redirect the user to the authorization URL.
        header('Location: '.$authorizationUrl);

        exit;

        // Check given state against previously stored one to mitigate CSRF attack
    }

    try {
        $tokens = $provider->getAccessToken('authorization_code', ['code' => $code]);

        $token->store($tokens);

        $secondsToExpire = \max(0, $tokens->getExpires() - \time());

        WebPage::singleton()->container->addItem(
            new \Ease\Html\DivTag(
                _('Success').': '.sprintf(_('Access token will expire in %d minutes. Refresh token is valid for 180 days.'), (int) \floor($secondsToExpire / 60)),
                ['class' => 'alert alert-success'],
            ),
        );

        WebPage::singleton()->container->addItem(new \Ease\Html\DivTag([
            new \Ease\TWB5\LinkButton('token.php?id='.$token->getMyKey(), '🔑 '._('Token'), 'success'),
            ' ',
            new \Ease\TWB5\LinkButton('application.php?id='.$app->getMyKey(), '🏷 '._('Application'), 'primary'),
            ' ',
            new \Ease\TWB5\LinkButton('index.php', '🏠 '._('Applications'), 'secondary'),
        ]));
    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        // Failed to get the access token or user details.
        exit($e->getMessage());
    }

    WebPage::singleton()->addItem(new PageBottom());
    WebPage::singleton()->draw();
} else {
    header('Location: index.php');
}
