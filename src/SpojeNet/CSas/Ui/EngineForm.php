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
use Ease\TWB5\Form;

class EngineForm extends Form
{
    public $engine;

    /**
     * Formulář Bootstrapu.
     *
     * @param \Ease\SQL\Engine $engine        jméno formuláře
     * @param mixed            $formContents  prvky uvnitř formuláře
     * @param array            $tagProperties vlastnosti tagu například:
     *                                        array('enctype' => 'multipart/form-data')
     */
    public function __construct($engine, $formContents = null, $tagProperties = [])
    {
        $this->engine = $engine;
        $tagProperties['method'] = 'post';
        $tagProperties['name'] = $engine::class;
        parent::__construct($tagProperties, [], $formContents);
    }

    /**
     * Add Hidden ID & Class field.
     */
    public function finalize(): void
    {
        $recordID = $this->engine->getMyKey();
        $this->addItem(new InputHiddenTag('class', \get_class($this->engine)));

        if (null !== $recordID) {
            $this->addItem(new InputHiddenTag($this->engine->getKeyColumn(), $recordID));
        }

        $this->fillUp((array) $this->engine->getData());

        parent::finalize();
    }
}
