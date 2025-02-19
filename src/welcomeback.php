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

\Ease\TWB5\WebPage::singleton(new \Ease\TWB5\WebPage(_('CSAS Authorize')));
\Ease\TWB5\WebPage::singleton()->addItem(new \Ease\Html\ImgTag('images/csas-authorize.svg', _('CSAS Authorize'), ['align' => 'right']));
\Ease\TWB5\WebPage::singleton()->addItem(new \Ease\Html\H1Tag(_('CSAS Authorize')));

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
        // Optional, only required when PKCE is enabled.
        // Restore the PKCE code stored in the session.
        // $provider->setPkceCode($_SESSION['oauth2pkceCode']);
        // Try to get an access token using the authorization code grant.
        $tokens = $provider->getAccessToken('authorization_code', ['code' => $code]);

        $token->store($tokens);

        $tokens = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $tokens->getRefreshToken(),
        ]);

        //        echo 'access token:<textarea>'.$tokens->getToken().'</textarea>';
        //        echo 'refresh token:<textarea>'.$tokens->getRefreshToken().'</textarea>';
        //        // Using the access token, we may look up details about the
        //        // resource owner.
        //        $resourceOwner = $provider->getResourceOwner($tokens);
        //
        //        var_export($resourceOwner->toArray());
        //
        //        // The provider provides a way to get an authenticated API request for
        //        // the service, using the access token; it returns an object conforming
        //        // to Psr\Http\Message\RequestInterface.
        //        $request = $provider->getAuthenticatedRequest(
        //            'GET',
        //            'https://service.example.com/resource',
        //            $tokens
        //        );
    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        // Failed to get the access token or user details.
        exit($e->getMessage());
    }

    \Ease\TWB5\WebPage::singleton()->draw();
} else {
    header('Location: index.php');
}
