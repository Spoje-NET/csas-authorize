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
 * Description of Token.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class Token extends \Ease\SQL\Engine
{
    public string $myTable = 'token';

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

    public function store(\League\OAuth2\Client\Token\AccessToken $tokens): int
    {
        $this->setDataValue('access_token', $tokens->getToken());
        $this->setDataValue('refresh_token', $tokens->getRefreshToken());
        $this->setDataValue('expires_in', $tokens->getExpires());

        return $this->saveToSQL();
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
}
