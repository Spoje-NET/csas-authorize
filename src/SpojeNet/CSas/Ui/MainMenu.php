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
 * Description of MainMenu.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class MainMenu extends \Ease\Html\NavTag
{
    public function __construct()
    {
        $logoLink = new \Ease\Html\ATag('index.php', new \Ease\Html\ImgTag('images/csas-authorize.svg', 'CSAS Authorize', ['width' => '30', 'height' => '24', 'class' => 'd-inline-block align-text-top']), ['class' => 'navbar-brand']);
        $logoLink->addItem(_('CSAS Authorize'));
        $container = new \Ease\TWB5\Container($logoLink);

        $container->addItem(new \Ease\TWB5\LinkButton('index.php', _('Status'), 'iverse'));

        $container->addItem($this->navBarToggler());
        $container->addItem($this->navBarCollapse());
        parent::__construct($container, ['class' => 'navbar navbar-expand-lg navbar-light bg-light']);
    }

    public function navBarToggler()
    {
        return new \Ease\Html\ButtonTag(new \Ease\Html\SpanTag(null, ['class' => 'navbar-toggler-icon']), [
            'class' => 'navbar-toggler',
            'type' => 'button',
            'data-bs-toggle' => 'collapse',
            'data-bs-target' => '#navbarNav',
            'aria-controls' => 'navbarNav',
            'aria-expanded' => 'false',
            'aria-label' => _('Toggle navigation'),
        ]);
    }

    /**
     * Summary of navBarCollapse.
     *
     * @return \Ease\Html\DivTag
     */
    public function navBarCollapse()
    {
        //        $oUser = \Ease\Shared::user();

        $navbarNav = new \Ease\Html\UlTag(null, ['class' => 'navbar-nav ms-auto flex-nowrap navbar-expand mb-2 mb-lg-0', 'style' => '--bs-scroll-height: 100px;']);

        //        if ($oUser->isLogged()) {
        //            $navbarNav->addItemSmart(new \Ease\Html\ATag('myapps.php', new \Ease\Html\ImgTag('images/apps.svg', 'apps', ['height' => 20]).' '._('My Apps'), ['class' => 'nav-link']), ['class' => 'nav-item']);
        $navbarNav->addItemSmart(new \Ease\Html\ATag('application.php', '➕ '._('Add'), ['class' => 'nav-link']), ['class' => 'nav-item']);
        //            $navbarNav->addItemSmart(new \Ease\Html\ATag('logout.php', '🚪 '._('Logout'), ['class' => 'nav-link']), ['class' => 'nav-item']);
        //        } else {
        //            $navbarNav->addItemSmart(new \Ease\Html\ATag('createaccount.php', _('Sign On'), ['class' => 'nav-link']), ['class' => 'nav-item']);
        //            $navbarNav->addItemSmart(new \Ease\Html\ATag('login.php', _('Sign In'), ['class' => 'nav-link']), ['class' => 'nav-item']);
        //        }

        //        switch (get_class($oUser)) {
        //            case 'MultiFlexi\User':
        //                break;
        //            default:
        //                $this->addStatusMessage('Unknow user class type: ' . get_class($oUser), 'warning');
        //                break;
        //        }
        //

        return new \Ease\Html\DivTag($navbarNav, ['class' => 'collapse navbar-collapse', 'id' => 'navbarNav']);
    }
}
