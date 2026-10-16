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
$error = WebPage::getRequestValue('error');

if (empty($state) && empty($code) && empty($error)) {
    // The IDP may return the result in the URL fragment (e.g. #error=access_denied), invisible to the server
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><script>'
        .'if (window.location.hash.length > 1) { window.location.replace(window.location.pathname + "?" + window.location.hash.substring(1)); }'
        .' else { window.location.replace("index.php"); }'
        .'</script></head><body><a href="index.php">'._('Applications').'</a></body></html>';

    exit;
}

$token = new \SpojeNet\CSas\Token(null, ['keyColumn' => 'uuid']);

if (!empty($state)) {
    $token->loadFromSQL($state);
}

WebPage::singleton()->addItem(new PageTop(_('CSAS Authorize')));
WebPage::singleton()->container->addItem(new \Ease\Html\ImgTag('images/csas-authorize.svg', _('CSAS Authorize'), ['align' => 'right']));
WebPage::singleton()->container->addItem(new \Ease\Html\H1Tag(_('CSAS Authorize')));

if (!empty($error)) {
    // e.g. error=access_denied&error_description=USER_CANCELLED_ACCESS when the client rejected the consent
    WebPage::singleton()->container->addItem(new \Ease\Html\DivTag(
        _('Authorization was not granted').': '.htmlspecialchars($error.' '.(string) WebPage::getRequestValue('error_description')),
        ['class' => 'alert alert-danger'],
    ));

    $buttons = new \Ease\Html\DivTag();

    if ($token->getMyKey() && empty($token->getDataValue('access_token'))) {
        $appId = (int) $token->getDataValue('application_id');
        $environment = (string) $token->getDataValue('environment');
        $token->deleteFromSQL(); // Unfinished authorization record is useless now

        $buttons->addItem(new \Ease\TWB5\LinkButton('auth.php?'.http_build_query(['app' => $appId, 'env' => $environment]), '🔐 '._('Authorize again'), 'primary'));
        $buttons->addItem(' ');
        $buttons->addItem(new \Ease\TWB5\LinkButton('application.php?id='.$appId, '🏷 '._('Application'), 'secondary'));
        $buttons->addItem(' ');
    }

    $buttons->addItem(new \Ease\TWB5\LinkButton('index.php', '🏠 '._('Applications'), 'secondary'));
    WebPage::singleton()->container->addItem($buttons);
    WebPage::singleton()->addItem(new PageBottom());
    WebPage::singleton()->draw();

    exit;
}

if ($token->getMyKey() && !empty($token->getDataValue('access_token'))) {
    // The state identifies an authorization which was already completed: do not accept it twice
    WebPage::singleton()->container->addItem(new \Ease\Html\DivTag(_('This authorization has already been completed.'), ['class' => 'alert alert-warning']));
    WebPage::singleton()->container->addItem(new \Ease\TWB5\LinkButton('token.php?id='.$token->getMyKey(), '🔑 '._('Token'), 'success'));
    WebPage::singleton()->addItem(new PageBottom());
    WebPage::singleton()->draw();

    exit;
}

if ($token->getMyKey() && !empty($code)) {
    $app = new \SpojeNet\CSas\Application($token->getDataValue('application_id'), ['autoload' => true]);
    $app->sandboxMode($token->getDataValue('environment') === 'sandbox');

    $provider = new \SpojeNet\CSas\Auth($app);

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

        // Offer removal of the tokens replaced by this authorization
        $obsoleteTokens = $token->getObsoleteTokens();

        if ($obsoleteTokens) {
            $oldTable = new \Ease\TWB5\Table();
            $oldTable->addRowHeaderColumns([_('Token'), _('Created'), _('Refresh Token'), _('Status'), '']);
            $unusable = 0;

            foreach ($obsoleteTokens as $old) {
                $unusable += $old['usable'] ? 0 : 1;
                $oldTable->addRowColumns([
                    new \Ease\Html\ATag('token.php?id='.$old['id'], $old['uuid']),
                    $old['created_at'],
                    empty($old['refresh_token']) ? '❌' : '✅',
                    $old['usable'] ? _('still valid') : _('unusable'),
                    new \Ease\TWB5\LinkButton('token.php?id='.$old['id'].'&action=delete&csrf='.\SpojeNet\CSas\Token::csrfToken(), '🗑 '._('Remove'), 'danger btn-sm', ['onclick' => "return confirm('".addslashes(_('Remove this token?'))."')"]),
                ]);
            }

            WebPage::singleton()->container->addItem(new \Ease\Html\H3Tag(_('Previous tokens of this application')));
            WebPage::singleton()->container->addItem(new \Ease\Html\DivTag(_('The new token replaces the older ones. Remove those which are not needed any more; a token which is still valid may be used by an integration.'), ['class' => 'alert alert-warning']));
            WebPage::singleton()->container->addItem($oldTable);

            if ($unusable) {
                WebPage::singleton()->container->addItem(new \Ease\TWB5\LinkButton('token.php?id='.$token->getMyKey().'&action=purge&csrf='.\SpojeNet\CSas\Token::csrfToken(), '🧹 '.sprintf(_('Remove all %d unusable tokens'), $unusable), 'danger', ['onclick' => "return confirm('".addslashes(_('Remove all unusable tokens?'))."')"]));
            }
        }
    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        // Failed to exchange the code for tokens
        $errorData = $e->getResponseBody();
        $errorDescription = \is_array($errorData) && isset($errorData['error_description']) ? $errorData['error_description'] : $e->getMessage();

        WebPage::singleton()->container->addItem(new \Ease\Html\DivTag(
            _('Code exchange failed').': '.htmlspecialchars((string) $errorDescription),
            ['class' => 'alert alert-danger'],
        ));
        WebPage::singleton()->container->addItem(new \Ease\TWB5\LinkButton('auth.php?'.http_build_query(['app' => $app->getMyKey(), 'env' => $token->getDataValue('environment')]), '🔐 '._('Authorize again'), 'primary'));
    }

    WebPage::singleton()->addItem(new PageBottom());
    WebPage::singleton()->draw();
} else {
    header('Location: index.php');
}
