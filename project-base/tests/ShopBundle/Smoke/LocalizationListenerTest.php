<?php

namespace Tests\ShopBundle\Smoke;

use Shopsys\ShopBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\Component\Router\CurrentDomainRouter;
use Shopsys\ShopBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\ShopBundle\Test\DatabaseTestCase;

class LocalizationListenerTest extends DatabaseTestCase
{
    public function testProductDetailOnFirstDomainHasCzechLocale()
    {
        $router = $this->getServiceByType(CurrentDomainRouter::class);
        /* @var $router \Shopsys\ShopBundle\Component\Router\CurrentDomainRouter */
        $productUrl = $router->generate('front_product_detail', ['id' => 3], UrlGeneratorInterface::ABSOLUTE_URL);

        $crawler = $this->getClient()->request('GET', $productUrl);

        $this->assertSame(200, $this->getClient()->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Katalogové číslo")')->count()
        );
    }

    /**
     * @group multidomain
     */
    public function testProductDetailOnSecondDomainHasEnglishLocale()
    {
        $domain = $this->getServiceByType(Domain::class);
        /* @var $domain \Shopsys\ShopBundle\Component\Domain\Domain */

        $domain->switchDomainById(2);

        $router = $this->getServiceByType(DomainRouterFactory::class)->getRouter(2);
        /* @var $router \Symfony\Component\Routing\RouterInterface */
        $productUrl = $router->generate('front_product_detail', ['id' => 3], UrlGeneratorInterface::ABSOLUTE_URL);
        $crawler = $this->getClient()->request('GET', $productUrl);

        $this->assertSame(200, $this->getClient()->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Catalogue number")')->count()
        );
    }
}
