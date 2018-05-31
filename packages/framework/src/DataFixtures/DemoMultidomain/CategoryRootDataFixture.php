<?php

namespace Shopsys\FrameworkBundle\DataFixtures\DemoMultidomain;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Category\CategoryDataFactory;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;

class CategoryRootDataFixture extends AbstractReferenceFixture
{
    const CATEGORY_ROOT = 'category_root';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryDataFactory
     */
    protected $categoryDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryDataFactory $categoryDataFactory
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        CategoryDataFactory $categoryDataFactory
    ) {
        $this->categoryDataFactory = $categoryDataFactory;
        $this->categoryFacade = $categoryFacade;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $rootCategory = $this->getReference(self::CATEGORY_ROOT);
        /* @var $rootCategory \Shopsys\FrameworkBundle\Model\Category\Category */

        $rootCategoryData = $this->categoryDataFactory->createFromCategory($rootCategory);

        $rootCategory->edit($rootCategoryData);
        $manager->persist($rootCategory);
        $manager->flush($rootCategory);
    }
}
