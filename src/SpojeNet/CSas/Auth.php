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

use Ease\Functions as Fnc;
use Ease\Shared as Shr;

/**
 * Description of Auth.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class Auth extends \League\OAuth2\Client\Provider\GenericProvider
{
    public const PRODUCTION_SITE = 'https://bezpecnost.csas.cz/api/psd2/fl/oidc/v1';
    public const SANDBOX_SITE = 'https://webapi.developers.erstegroup.com/api/csas/sandbox/v1/sandbox-idp';
    private string $idpLink;

    public function __construct()
    {
        $this->idpLink = Shr::cfg('CSAS_SANDBOX_MODE', false) ? self::SANDBOX_SITE : self::PRODUCTION_SITE;

        // Use environment variables
        $clientId = Shr::cfg('CSAS_CLIENT_ID');
        $clientSecret = Shr::cfg('CSAS_CLIENT_SECRET');
        $redirectUri = Shr::cfg('CSAS_REDIRECT_URI');

        $tokenUrl = $this->idpLink.'/token';
        $authorizeUrl = $this->idpLink.'/authorize';
        $resourceUrl = $this->idpLink.'/resource';

        parent::__construct([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => $redirectUri,
            'urlAuthorize' => $authorizeUrl,
            'urlAccessToken' => $tokenUrl,
            'urlResourceOwnerDetails' => $resourceUrl,
        ]);
    }

    public function getIdpUri(): string
    {
        /**
         * @see https://developers.erstegroup.com/docs/tutorial/csas-how-to-call-api Authentization & Authorization
         *
         * @var array<string, string> Authorization link parameters
         */
        $idpParams = [
            'client_id' => Shr::cfg('CSAS_CLIENT_ID'),
            'response_type' => 'code',
            'redirect_uri' => Shr::cfg('CSAS_REDIRECT_URI'),
            'state' => Fnc::randomString(),
            'access_type' => 'offline',
            //    'scope' => implode('%20', [
            //        'siblings.accounts',
            //        //        'siblings.payments',
            //        //        'AISP',
            //        //        'PISP'
            //    ]),
        ];

        return Fnc::addUrlParams($this->idpLink.'/auth', $idpParams);
    }
}
