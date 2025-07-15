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
 * Description of AppTable.
 *
 * @author Vitex <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class AppTable extends \Ease\TWB5\Table
{
    public function __construct(\SpojeNet\CSas\Application $app)
    {
        $apps = $app->listingQuery();
        parent::__construct();
        $this->addRowHeaderColumns(['#', '🖼️']);

        foreach ($apps as $appDataRaw) {
            $appData['id'] = new \Ease\TWB5\LinkButton('application.php?id='.$appDataRaw['id'], $appDataRaw['id'], 'link');

            if ($appDataRaw['logo']) {
                $appData['logo'] = new \Ease\Html\ImgTag($appDataRaw['logo'], $appDataRaw['name'], ['height' => 40]);
            }

            $appData['name'] = new \Ease\Html\ATag('application.php?id='.$appDataRaw['id'], $appDataRaw['name']);

            $this->addRowColumns($appData);
        }
    }
}
