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
 * Description of TokenInfo.
 *
 * @author Vitex <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class TokenInfo extends \Ease\Html\DivTag
{
    public function __construct(\SpojeNet\CSas\Token $token)
    {
        parent::__construct(new \Ease\Html\H3Tag($token->getDataValue('uuid')));
        $this->addItem(new \Ease\Html\DivTag($token->getDataValue('environment')));

        if ($token->getDataValue('expires_in')) {
            $expiresAt = (new \DateTime())->setTimestamp($token->getDataValue('expires_in'));
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

            $this->addItem(new \Ease\Html\DivTag(_('Created At').' '.$token->getDataValue('created_at').' ('.new \Ease\Html\Widgets\LiveAge(new \DateTime($token->getDataValue('created_at'))).' )'));
            $this->addItem(new \Ease\Html\DivTag(_('Expires At').' '.$expiresAt->format('Y-m-d H:i:s').' ('.new \Ease\Html\Widgets\LiveAge($expiresAt, ['class' => $class]).' )'));
        }

        $this->addItem(new \Ease\Html\DivTag(_('Access Token')));
        $this->addItem(new \Ease\Html\TextareaTag('access_token', $token->getDataValue('access_token'), ['style' => 'field-sizing: content;', 'cols' => 80]));

        // Check refresh token expiration
        if ($token->getDataValue('created_at')) {
            $refreshCreated = new \DateTime($token->getDataValue('created_at'));
            $refreshExpiresAt = $refreshCreated->modify('+ 180 days');

            $refreshInterval = $now->diff($refreshExpiresAt);

            if ($refreshExpiresAt < $now || $refreshInterval->days < 1) {
                $refreshClass = 'text-danger';
            } else {
                $refreshClass = 'text-default';
            }

            $this->addItem(new \Ease\Html\DivTag(_('Refresh Token Expires At').' '.$refreshExpiresAt->format('Y-m-d H:i:s').' ('.new \Ease\Html\Widgets\LiveAge($refreshExpiresAt, ['class' => $refreshClass]).' )'));
            $this->addItem(new \Ease\Html\DivTag(sprintf(_('Expire in %d days'), $refreshInterval->days)));
        }

        $this->addItem(new \Ease\Html\DivTag(_('Refresh Token')));
        $this->addItem(new \Ease\Html\TextareaTag('refresh_token', $token->getDataValue('refresh_token'), ['style' => 'field-sizing: content;', 'cols' => 80]));
    }
}
