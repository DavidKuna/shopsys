<?php

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\ShopBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\ShopBundle\Model\Country\CountryData;

class CountryDataFixture extends AbstractReferenceFixture
{
    const COUNTRY_CZECH_REPUBLIC_1 = 'country_czech_republic_1';
    const COUNTRY_SLOVAKIA_1 = 'country_slovakia_1';

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $domainId = 1;
        $countryData = new CountryData();
        $countryData->name = 'Česká republika';
        $this->createCountry($countryData, $domainId, self::COUNTRY_CZECH_REPUBLIC_1);

        $countryData = new CountryData();
        $countryData->name = 'Slovenská republika';
        $this->createCountry($countryData, $domainId, self::COUNTRY_SLOVAKIA_1);
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Country\CountryData $countryData
     * @param int $domainId
     * @param string $referenceName
     */
    private function createCountry(CountryData $countryData, $domainId, $referenceName)
    {
        $countryFacade = $this->get('shopsys.shop.country.country_facade');
        /* @var $countryFacade \Shopsys\ShopBundle\Model\Country\CountryFacade */

        $country = $countryFacade->create($countryData, $domainId);
        $this->addReference($referenceName, $country);
    }
}
