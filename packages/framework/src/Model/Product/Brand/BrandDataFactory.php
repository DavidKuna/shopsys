<?php

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class BrandDataFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    protected $friendlyUrlFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade
     */
    protected $brandFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    public function __construct(
        FriendlyUrlFacade $friendlyUrlFacade,
        BrandFacade $brandFacade,
        Domain $domain
    ) {
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->brandFacade = $brandFacade;
        $this->domain = $domain;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\BrandData
     */
    public function createDefault()
    {
        $brandData = new BrandData();

        foreach ($this->domain->getAllIds() as $id) {
            $brandData->seoMetaDescriptions[$id] = null;
            $brandData->seoTitles[$id] = null;
            $brandData->seoH1s[$id] = null;
        }

        return $brandData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\BrandData
     */
    public function createFromBrand(Brand $brand)
    {
        $brandData = new BrandData();
        $brandData->name = $brand->getName();

        $translations = $brand->getTranslations();
        /* @var $translations \Shopsys\FrameworkBundle\Model\Product\Brand\BrandTranslation[]  */

        $brandData->descriptions = [];
        foreach ($translations as $translation) {
            $brandData->descriptions[$translation->getLocale()] = $translation->getDescription();
        }

        $allDomainIds = $this->domain->getAllIds();
        foreach ($allDomainIds as $domainId) {
            $brandData->seoH1s[$domainId] = $brand->getSeoH1($domainId);
            $brandData->seoTitles[$domainId] = $brand->getSeoTitle($domainId);
            $brandData->seoMetaDescriptions[$domainId] = $brand->getSeoMetaDescription($domainId);

            $brandData->urls->mainFriendlyUrlsByDomainId[$domainId] =
                $this->friendlyUrlFacade->findMainFriendlyUrl(
                    $domainId,
                    'front_brand_detail',
                    $brand->getId()
                );
            $brandData->seoTitles[$domainId] = $brandDomain->getSeoTitle();
            $brandData->seoMetaDescriptions[$domainId] = $brandDomain->getSeoMetaDescription();
            $brandData->seoH1s[$domainId] = $brandDomain->getSeoH1();
        }

        return $brandData;
    }
}
