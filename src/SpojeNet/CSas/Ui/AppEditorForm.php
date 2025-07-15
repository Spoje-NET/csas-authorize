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
use Ease\TWB5\SubmitButton;

/**
 * Description of RegisterAppForm.
 *
 * Author: vitex
 *
 * @no-named-arguments
 */
class AppEditorForm extends EngineForm
{
    public function afterAdd(): void
    {
        $this->addInput(new InputTextTag('name'), _('Application name'));
        $this->addInput(new InputTextTag('uuid'), _('Application ID'));
        $this->addInput(new InputTextTag('logo'), _('Logo URL'));
        $this->addInput(new InputTextTag('sandbox_client_id'), _('Sandbox Client ID'));
        $this->addInput(new InputTextTag('sandbox_client_secret'), _('Sandbox Client Secret'));
        $this->addInput(new InputTextTag('sandbox_redirect_uri'), _('Sandbox Redirect URI'));
        $this->addInput(new InputTextTag('sandbox_api_key'), _('Sandbox API Key'));
        $this->addInput(new InputTextTag('production_client_id'), _('Production Client ID'));
        $this->addInput(new InputTextTag('production_client_secret'), _('Production Client Secret'));
        $this->addInput(new InputTextTag('production_redirect_uri'), _('Production Redirect URI'));
        $this->addInput(new InputTextTag('production_api_key'), _('Production API Key'));
        $this->addInput(new \Ease\Html\InputEmailTag('email'), _('Email for Renewal Notifications'));

        $this->addItem(new SubmitButton(_('Save'), 'success'));

        if (null !== $this->engine->getDataValue('id')) {
            $this->addItem(new InputHiddenTag('id'));
        }

        if ($this->engine->getDataCount()) {
            $this->fillUp($this->engine->getData());
        }
    }
}
