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

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Description of Token.
 *
 * Author: Vitex <info@vitexsoftware.cz>
 */
class Token extends \Ease\SQL\Engine
{
    public string $myTable = 'token';
    public string $nameColumn = 'uuid';
    public ?string $createColumn = 'created_at';
    public ?string $lastModifiedColumn = 'updated_at';
    private Application $application;

    public function takeData($data): int
    {
        if (\array_key_exists('application_id', $data)) {
            $this->application = new Application($data['application_id'], ['autoload' => true]);

            if (\array_key_exists('environment', $data)) {
                $this->application->sandboxMode($data['environment'] === 'sandbox');
            }
        }

        return parent::takeData($data);
    }

    public function getNextTokenUuid(): string
    {
        $this->setDataValue('uuid', \Ease\Functions::guidv4());

        return $this->dbSync() ? $this->getDataValue('uuid') : throw new \RuntimeException(_('Error Creating Token Record'), 22);
    }

    public function setApplication(Application $app): void
    {
        $this->setDataValue('application_id', $app->getMyKey());
        $this->setDataValue('environment', $app->sandboxMode() ? 'sandbox' : 'production');

        $this->application = $app;
    }

    public function store(\League\OAuth2\Client\Token\AccessTokenInterface $tokens): bool
    {
        $this->setDataValue('access_token', $tokens->getToken());
        $refreshToken = $tokens->getRefreshToken();

        if (empty($refreshToken) === false) {
            $this->setDataValue('refresh_token', $refreshToken);
        }

        $this->setDataValue('expires_in', $tokens->getExpires());
        $this->unsetDataValue('created_at');
        $this->unsetDataValue('updated_at');

        return $this->dbSync();
    }

    public function getAppTokens(Application $app)
    {
        return $this->listingQuery()->where(['application_id' => $app->getMyKey()]);
    }

    public function isExpired(): bool
    {
        $expiresIn = $this->getDataValue('expires_in');

        return $expiresIn !== null && $expiresIn < time();
    }

    /**
     * Request new access token and store it.
     *
     * @throws \RuntimeException
     */
    public function refreshToken(AbstractProvider $provider): AccessToken
    {
        $refreshToken = $this->getDataValue('refresh_token');

        if (empty($refreshToken)) {
            throw new \RuntimeException(_('No refresh token available'), 23);
        }

        $newToken = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $refreshToken,
        ]);

        $this->store($newToken);
        $expiresAt = (new \DateTime())->setTimestamp($this->getDataValue('expires_in'));
        $this->addStatusMessage(sprintf(_('Token Refreshed. Valid till: %s'), $expiresAt->format('Y-m-d H:i:s')), 'success');

        return $newToken;
    }

    public function secondsToExpire(string $columnName): int
    {
        /**
         * @var int|string Time can be in seconds or SQL datetime
         */
        $expiresRaw = $this->getDataValue($columnName);

        if (is_numeric($expiresRaw)) {
            $expiresAt = (new \DateTime())->setTimestamp($expiresRaw);
        } else {
            $expiresAt = new \DateTime($expiresRaw);
        }

        return $expiresAt->getTimestamp() - time();
    }

    public function daysToExpire(string $columnName): int
    {
        $seconds = $this->secondsToExpire($columnName);

        return (int) floor($seconds / 86400);
    }

    /**
     * Validity od refresh token is 180 days only.
     */
    public function tokenRenewInDays(): int
    {
        return $this->daysToExpire('created_at');
    }

    public function getTokensCloseToExpire()
    {
        $oneWeekFromNow = (new \DateTime())->modify('+1 week')->getTimestamp();

        return $this->listingQuery()->select(['application.name', 'application.email', 'production_redirect_uri', 'sandbox_redirect_uri'])->leftJoin('application ON application.id = token.application_id')
            ->where('expires_in', '<'.$oneWeekFromNow)
            ->fetchAll();
    }

    /**
     * The Access Token can be used only 5minutes.
     */
    public function tokenValiditySeconds(): int
    {
        return (int) $this->secondsToExpire('expires_in');
    }

    public function getSandBoxMode(): bool
    {
        return $this->getDataValue('environment') === 'sandbox';
    }

    /**
     * Get Api Connection Fields.
     *
     * @return array<string, string> Environment
     */
    public function exportEnv(): array
    {
        return [
            '#CSAS_TOKEN_UUID' => $this->getDataValue('uuid'),
            'CSAS_API_KEY' => $this->application->getApiKey(),
            'CSAS_SANDBOX_MODE' => $this->getSandBoxMode() ? 'true' : 'false',
            'CSAS_ACCESS_TOKEN' => $this->getDataValue('access_token'),
        ];
    }

    public function getApplication(): Application
    {
        return $this->application;
    }
}
