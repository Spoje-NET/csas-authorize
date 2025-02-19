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

/**
 * Description of TokenTable.
 *
 * Author: Vitex <info@vitexsoftware.cz>
 */
class AppTokenTable extends \Ease\TWB5\Table
{
    public function __construct(\SpojeNet\CSas\Application $application)
    {
        parent::__construct();
        $this->addRowHeaderColumns(['#', _('Environment'), _('Access Token'), _('Refresh Token'), _('Expiration'), _('Created'), _('Updated')]);
        $tokens = new \SpojeNet\CSas\Token();

        foreach ($tokens->getAppTokens($application)->fetchAll() as $tokenData) {
            unset($tokenData['application_id'], $tokenData['uuid'], $tokenData['scope']);

            $tokenData['access_token'] = empty($tokenData['access_token']) ? '❌' : '✅';
            $tokenData['refresh_token'] = empty($tokenData['refresh_token']) ? '❌' : '✅';

            if ($tokenData['expires_in']) {
                $expiresAt = (new \DateTime())->setTimestamp($tokenData['expires_in']);
                $now = new \DateTime();
                $interval = $now->diff($expiresAt);

                if ($expiresAt < $now) {
                    $class = 'text-danger';
                } elseif ($interval->days < 7) {
                    $class = 'text-warning';
                } elseif ($interval->days > 150) {
                    $class = 'text-success';
                } else {
                    $class = 'text-default';
                }

                $tokenData['expires_in'] = new \Ease\Html\Widgets\LiveAge($expiresAt, ['class' => $class]);
            }

            $tokenData['id'] = new \Ease\TWB5\LinkButton('token.php?id='.$tokenData['id'], $tokenData['id'], 'link');

            $this->addRowColumns($tokenData);
        }
    }
}
