<?php

namespace Shopsys\FrameworkBundle\Model\Category;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;
use Shopsys\FrameworkBundle\Form\UrlListData;

class CategoryData
{
    /**
     * @var string[]
     */
    public $name;

    /**
     * @var string[]|null[]
     */
    public $seoTitles;

    /**
     * @var string[]|null[]
     */
    public $seoMetaDescriptions;

    /**
     * @var string[]|null[]
     */
    public $seoH1s;

    /**
     * @var string[]
     */
    public $descriptions;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\Category|null
     */
    public $parent;

    /**
     * @var bool[]
     */
    public $enabled;

    /**
     * @var \Shopsys\FrameworkBundle\Form\UrlListData
     */
    public $urls;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $image;

    /**
     * @var array
     */
    public $pluginData;

    public function __construct()
    {
        $this->name = [];
        $this->seoTitles = [];
        $this->seoMetaDescriptions = [];
        $this->seoH1s = [];
        $this->descriptions = [];
        $this->enabled = [];
        $this->urls = new UrlListData();
        $this->image = new ImageUploadData();
        $this->pluginData = [];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     */
    public function setFromEntity(Category $category)
    {
        $translations = $category->getTranslations();
        $categoryDomains = $category->getCategoryDomains();

        $names = [];
        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
        }
        $this->name = $names;
        $this->parent = $category->getParent();
        foreach ($categoryDomains as $categoryDomain) {
            $domainId = $categoryDomain->getDomainId();

            $this->seoTitles[$domainId] = $category->getSeoTitle($domainId);
            $this->seoMetaDescriptions[$domainId] = $category->getSeoMetaDescription($domainId);
            $this->seoH1s[$domainId] = $category->getSeoH1($domainId);
            $this->descriptions[$domainId] = $category->getDescription($domainId);
            $this->enabled[$domainId] = $category->isEnabled($domainId);
        }
    }
}
