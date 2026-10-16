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
 * Description of Application.
 *
 * @author Vitex <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class Application extends \Ease\SQL\Engine
{
    public string $myTable = 'application';
    private $sandboxed = false;

    public static function getImage(string $appUuid): string
    {
        return 'https://webapi.developers.erstegroup.com/api/v1/file-manager/files2/'.$appUuid.'/image/small';
    }

    public function takeData(array $data): int
    {
        unset($data['class']);

        return parent::takeData($data);
    }

    public function getUuid(): string
    {
        return $this->getDataValue('uuid');
    }

    public function sandboxMode(?bool $enabled = null): bool
    {
        if ((null === $enabled) === false) {
            $this->sandboxed = $enabled;
        }

        return $this->sandboxed;
    }

    public function getApiKey(): ?string
    {
        return $this->getDataValue($this->sandboxed ? 'sandbox_api_key' : 'production_api_key');
    }

    public function getClientId(): ?string
    {
        return $this->getDataValue($this->sandboxed ? 'sandbox_client_id' : 'production_client_id');
    }

    public function getClientSecret(): ?string
    {
        return $this->getDataValue($this->sandboxed ? 'sandbox_client_secret' : 'production_client_secret');
    }

    public function getRedirectUri(): ?string
    {
        return $this->getDataValue($this->sandboxed ? 'sandbox_redirect_uri' : 'production_redirect_uri');
    }

    public function getToken(): Token
    {
        $tokener = new Token();
        $tokener->setApplication($this);

        return $tokener;
    }

    public function pruneTokens(): void
    {
        $tokens = new Token();

        foreach ($tokens->listingQuery()->where(['application_id' => $this->getMyKey(), 'expires_in < '.time()]) as $tokenData) {
            $tokens->addStatusMessage('🧹 '._('Removing  Expired Token').$tokenData['id'], 'info');
            $tokens->deleteFromSQL(['id' => $tokenData['id']]);
        }
    }

    public function hasSandboxRedirectUri(): bool
    {
        return !empty($this->getDataValue('sandbox_redirect_uri'));
    }

    public function hasProductionRedirectUri(): bool
    {
        return !empty($this->getDataValue('production_redirect_uri'));
    }

    /**
     * Recipient of the authorization link: application email or the one requested.
     */
    public function getAuthorizationRecipient(): ?string
    {
        return $this->getDataValue('email') ?? \Ease\WebPage::getRequestValue('email');
    }

    /**
     * Sends an authorization link by email instead of redirecting the browser.
     */
    public function sendAuthorizationLinkByEmail(): bool
    {
        $auth = new \SpojeNet\CSas\Auth($this);

        $idpUri = $auth->getIdpUri();

        // Get recipient email from application data or request
        $recipientEmail = $this->getAuthorizationRecipient();

        if (empty($recipientEmail)) {
            \Ease\WebPage::singleton()->addStatusMessage(_('Recipient email address is not set.'), 'error');
            header('Location: application.php?id='.$this->getMyKey());

            exit;
        }

        $subject = _('CSAS Authorization Link');
        $message = sprintf(
            _("Hello,\n\nPlease use the following link to authorize your application:\n%s\n\nBest regards,\nCSAS Authorize"),
            $idpUri,
        );

        return (new Mailer())->sendMessage($recipientEmail, $subject, $message);
    }
}
