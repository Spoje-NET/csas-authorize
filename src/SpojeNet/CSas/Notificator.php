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

namespace SpojeNet\CSas;

/**
 * Description of Notificator.
 *
 * Author: Vitex <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class Notificator extends \Ease\HtmlMailer
{
    public function __construct(Token $token)
    {
        $mailBody = new \Ease\Html\DivTag();

        $mailBody->addItem(new \Ease\Html\H1Tag(sprintf(_('Token %s renew'), $token->getDataValue('name'))));
        $mailBody->addItem(new \Ease\Html\DivTag(sprintf(_('Token %s renew'), $token->getDataValue('name'))));

        $redirectUri = $token->getDataValue('environment') === 'sandbox' ? $token->getDataValue('sandbox_redirect_uri') : $token->getDataValue('production_redirect_uri');

        $refreshCreated = new \DateTime($token->getDataValue('created_at'));

        $expirationDate = $refreshCreated->modify('+ 180 days');
        $currentDate = new \DateTime();
        $remainingTime = $currentDate->diff($expirationDate);

        $mailBody->addItem(new \Ease\Html\PTag(sprintf(_('Token expiration date: %s'), $expirationDate->format('Y-m-d H:i:s'))));
        $mailBody->addItem(new \Ease\Html\PTag(sprintf(_('Remaining time: %d days, %d hours, %d minutes'), $remainingTime->days, $remainingTime->h, $remainingTime->i)));

        $renewalLink = $this->buildRenewalLink($redirectUri, (int) $token->getDataValue('application_id'), (string) $token->getDataValue('environment'));

        $mailBody->addItem(new \Ease\Html\ATag($renewalLink, '♻️ '._('Renew'), ['style' => 'font-size: xxx-large;']));

        $hostname = gethostname();
        $username = get_current_user();
        $fromEmail = \Ease\Shared::cfg('EMAIL_FROM', "{$username}@{$hostname}");
        parent::__construct((string) ($token->getDataValue('email') ?? ''), sprintf(_('%s token renew'), $token->getDataValue('name')), (string) $mailBody, ['From' => $fromEmail]);
    }

    /**
     * Build token renewal link from the application redirect URI.
     *
     * Replaces the callback script in the redirect URI with auth.php
     * regardless of the actual callback filename or path depth.
     *
     * @param string $redirectUri   Registered OAuth2 redirect URI (points to welcomeback.php)
     * @param int    $applicationId Application database ID
     * @param string $environment  sandbox|production
     *
     * @return string Renewal link pointing to auth.php on the same host
     */
    private function buildRenewalLink(string $redirectUri, int $applicationId, string $environment): string
    {
        $urlParts = \parse_url($redirectUri);

        if (empty($urlParts['host'])) {
            return $redirectUri;
        }

        $path = $urlParts['path'] ?? '/welcomeback.php';
        $baseDir = \rtrim(\dirname($path), '/');

        $query = \http_build_query(['app' => $applicationId, 'env' => $environment]);

        return ($urlParts['scheme'] ?? 'https').'://'.$urlParts['host']
            .(isset($urlParts['port']) ? ':'.$urlParts['port'] : '')
            .$baseDir.'/auth.php?'.$query;
    }
}
