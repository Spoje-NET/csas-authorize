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

namespace SpojeNet\CSas\Tests\Ui;

use PHPUnit\Framework\TestCase;
use SpojeNet\CSas\Ui\WebPage;

/**
 * WebPage Test Class.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class WebPageTest extends TestCase
{
    public function testConstructorWithoutTitle(): void
    {
        $webpage = new WebPage();

        $this->assertInstanceOf(WebPage::class, $webpage);
        $this->assertInstanceOf(\Ease\TWB5\WebPage::class, $webpage);
    }

    public function testConstructorWithTitle(): void
    {
        $webpage = new WebPage('Test Page Title');

        $this->assertInstanceOf(WebPage::class, $webpage);
    }

    public function testHasContainerProperty(): void
    {
        $webpage = new WebPage('Test Container');

        $this->assertInstanceOf(\Ease\TWB5\Container::class, $webpage->container);
    }

    public function testContainerHasFluidClass(): void
    {
        $webpage = new WebPage('Test Fluid');

        // Check if container has the container-fluid class
        $containerClasses = $webpage->container->getTagClass();
        $this->assertStringContainsString('container-fluid', $containerClasses);
    }

    public function testInheritsFromTWB5WebPage(): void
    {
        $webpage = new WebPage('Inheritance Test');

        $this->assertInstanceOf(\Ease\TWB5\WebPage::class, $webpage);
    }

    public function testConstructorWithNullTitle(): void
    {
        $webpage = new WebPage(null);

        $this->assertInstanceOf(WebPage::class, $webpage);
    }

    public function testConstructorWithEmptyTitle(): void
    {
        $webpage = new WebPage('');

        $this->assertInstanceOf(WebPage::class, $webpage);
    }

    public function testConstructorWithSpecialCharactersInTitle(): void
    {
        $webpage = new WebPage('Tëst Pàgé with Spéciâl Chäractërs');

        $this->assertInstanceOf(WebPage::class, $webpage);
    }

    public function testMultipleInstancesAreIndependent(): void
    {
        $webpage1 = new WebPage('First Page');
        $webpage2 = new WebPage('Second Page');

        $this->assertInstanceOf(WebPage::class, $webpage1);
        $this->assertInstanceOf(WebPage::class, $webpage2);
        $this->assertNotSame($webpage1, $webpage2);
        $this->assertNotSame($webpage1->container, $webpage2->container);
    }
}
