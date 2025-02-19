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
        return $this->getDataValue('');
    }

    public function sandboxMode(?bool $enabled = null): bool
    {
        if ((null === $enabled) === false) {
            $this->sandboxed = $enabled;
        }

        return $this->sandboxed;
    }

    public function getApiKey(): string
    {
        return $this->getDataValue($this->sandboxed ? 'sandbox_api_key' : 'production_api_key');
    }

    public function getClientId(): string
    {
        return $this->getDataValue($this->sandboxed ? 'sandbox_client_id' : 'production_client_id');
    }

    public function getClientSecret(): string
    {
        return $this->getDataValue($this->sandboxed ? 'sandbox_client_secret' : 'production_client_secret');
    }

    public function getRedirectUri(): string
    {
        return $this->getDataValue($this->sandboxed ? 'sandbox_redirect_uri' : 'production_redirect_uri');
    }

    public function getToken(): Token
    {
        $tokener = new Token();
        $tokener->setApplication($this);

        return $tokener;
    }
    
}
