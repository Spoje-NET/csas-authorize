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

use Ease\Html\InputHiddenTag;
use Ease\Html\InputTextTag;
use Ease\TWB5\InputGroup;
use Ease\TWB5\SubmitButton;

/**
 * Description of OffCanvasForm.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class OffCanvasForm extends EngineForm
{
    public function offCanvas($id, $title, $bodyContent)
    {
        //        $this->addItem(
        //            new \Ease\Html\ButtonTag($title, [
        //                'class' => 'btn btn-primary',
        //                'type' => 'button',
        //                'data-bs-toggle' => $id,
        //                'data-bs-target' => '#'.$id.'Handle',
        //                'aria-controls' => $id.'Handle',
        //            ]),
        //        );

        return $this->addItem(new \Ease\TWB5\OffCanvas($id, $title, $bodyContent));
    }

    public function afterAdd(): void
    {
        $appFields = new \Ease\Html\DivTag();
        $appFields->addItem(new InputGroup(_('Application name'), new InputTextTag('name')));
        $appFields->addItem(new InputGroup(_('Application ID'), new InputTextTag('uuid')));
        $appFields->addItem(new InputGroup(_('Logo URL'), new InputTextTag('logo')));
        $appFields->addItem(new InputGroup(_('Email for Renewal Notifications'), new \Ease\Html\InputEmailTag('email')));
        $appFields->addItem(new SubmitButton(_('Save'), 'success'));
        $this->offCanvas('applicationOffCanvas', _('Application'), $appFields);

        //        $sandBoxFields = new \Ease\Html\DivTag();
        //        $sandBoxFields->addItem(new InputGroup(_('Sandbox Client ID'), new InputTextTag('sandbox_client_id'), '09f5eeb2-3abb-4a73-bed7-f6fa61976847', _('get from developer portal')));
        //        $sandBoxFields->addItem(new InputGroup(_('Sandbox Client Secret'), new InputTextTag('sandbox_client_secret'), 'f7b3b3b3-3b3b-4b3b-b3b3-3b3b3b3b3b3b', _('get from developer portal')));
        //        $sandBoxFields->addItem(new InputGroup(_('Sandbox Redirect URI'), new InputTextTag('sandbox_redirect_uri'), 'https://example.com/csas/authorize.php', _('URL where to redirect after authorization')));
        //        $sandBoxFields->addItem(new InputGroup(_('Sandbox API Key'), new InputTextTag('sandbox_api_key'), 'sandbox', _('API Key for sandbox environment')));
        //        $sandBoxFields->addItem(new SubmitButton(_('Save'), 'success'));
        //        $this->offCanvas('sandboxOffCanvas', _('SandBox'), $sandBoxFields);
        //
        //        $productionFields = new \Ease\Html\DivTag();
        //        $productionFields->addItem(new InputGroup(_('Production Client ID'), new InputTextTag('production_client_id'), _('Production Client ID'), _('get from developer portal')));
        //        $productionFields->addItem(new InputGroup(_('Production Client Secret'), new InputTextTag('production_client_secret'), _('Production Client Secret'), _('get from developer portal')));
        //        $productionFields->addItem(new InputGroup(_('Production Redirect URI'), new InputTextTag('production_redirect_uri'), _('Production Redirect URI'), _('URL where to redirect after authorization')));
        //        $productionFields->addItem(new InputGroup(_('Production API Key'), new InputTextTag('production_api_key'), _('Production API Key'), _('API Key for production environment')));
        //        $productionFields->addItem(new SubmitButton(_('Save'), 'success'));
        //
        //        $this->offCanvas('productionOffCanvas', _('Production'), $productionFields);

        if (null !== $this->engine->getDataValue('id')) {
            $this->addItem(new InputHiddenTag('id'));
        }

        if ($this->engine->getDataCount()) {
            $this->fillUp($this->engine->getData());
        }
    }
}
