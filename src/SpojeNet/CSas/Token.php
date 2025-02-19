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
    public ?string $createColumn = 'created_at';
    public ?string $lastModifiedColumn = 'updated_at';

    public function getNextTokenUuid(): string
    {
        $this->setDataValue('uuid', \Ease\Functions::guidv4());

        return $this->dbSync() ? $this->getDataValue('uuid') : throw new \RuntimeException(_('Error Creating Token Record'), 22);
    }

    public function setApplication(Application $app): void
    {
        $this->setDataValue('application_id', $app->getMyKey());
        $this->setDataValue('environment', $app->sandboxMode() ? 'sandbox' : 'production');
    }

    public function store(AccessToken $tokens): bool
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
        $this->addStatusMessage(_('Token Refreshed'), 'success');
        return $newToken;
    }
}
