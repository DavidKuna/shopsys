<?php

namespace Shopsys\ShopBundle\Controller\Front;

use Shopsys\ShopBundle\Component\Controller\FrontBaseController;
use Shopsys\ShopBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\Component\Paginator\Pagination;
use Shopsys\ShopBundle\Form\Front\Product\ProductFilterFormTypeFactory;
use Shopsys\ShopBundle\Model\Advert\AdvertPositionList;
use Shopsys\ShopBundle\Model\Category\Category;
use Shopsys\ShopBundle\Model\Category\CategoryFacade;
use Shopsys\ShopBundle\Model\Module\ModuleFacade;
use Shopsys\ShopBundle\Model\Module\ModuleList;
use Shopsys\ShopBundle\Model\Product\Brand\BrandFacade;
use Shopsys\ShopBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\ShopBundle\Model\Product\Listing\ProductListOrderingModeForBrandFacade;
use Shopsys\ShopBundle\Model\Product\Listing\ProductListOrderingModeForListFacade;
use Shopsys\ShopBundle\Model\Product\Listing\ProductListOrderingModeForSearchFacade;
use Shopsys\ShopBundle\Model\Product\ProductOnCurrentDomainFacade;
use Shopsys\ShopBundle\Twig\RequestExtension;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends FrontBaseController
{
    const SEARCH_TEXT_PARAMETER = 'q';
    const PRODUCTS_PER_PAGE = 12;

    /**
     * @var \Shopsys\ShopBundle\Component\Paginator\Pagination
     */
    private $pagination;

    /**
     * @var \Shopsys\ShopBundle\Form\Front\Product\ProductFilterFormTypeFactory
     */
    private $productFilterFormTypeFactory;

    /**
     * @var \Shopsys\ShopBundle\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var \Shopsys\ShopBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\ShopBundle\Model\Product\ProductOnCurrentDomainFacade
     */
    private $productOnCurrentDomainFacade;

    /**
     * @var \Shopsys\ShopBundle\Model\Product\Listing\ProductListOrderingModeForListFacade
     */
    private $productListOrderingModeForListFacade;

    /**
     * @var \Shopsys\ShopBundle\Model\Product\Listing\ProductListOrderingModeForBrandFacade
     */
    private $productListOrderingModeForBrandFacade;

    /**
     * @var \Shopsys\ShopBundle\Model\Product\Listing\ProductListOrderingModeForSearchFacade
     */
    private $productListOrderingModeForSearchFacade;

    /**
     * @var \Shopsys\ShopBundle\Model\Module\ModuleFacade
     */
    private $moduleFacade;

    /**
     * @var \Shopsys\ShopBundle\Model\Product\Brand\BrandFacade
     */
    private $brandFacade;

    public function __construct(
        Pagination $pagination,
        CategoryFacade $categoryFacade,
        Domain $domain,
        ProductOnCurrentDomainFacade $productOnCurrentDomainFacade,
        ProductFilterFormTypeFactory $productFilterFormTypeFactory,
        ProductListOrderingModeForListFacade $productListOrderingModeForListFacade,
        ProductListOrderingModeForBrandFacade $productListOrderingModeForBrandFacade,
        ProductListOrderingModeForSearchFacade $productListOrderingModeForSearchFacade,
        ModuleFacade $moduleFacade,
        BrandFacade $brandFacade
    ) {
        $this->pagination = $pagination;
        $this->categoryFacade = $categoryFacade;
        $this->domain = $domain;
        $this->productOnCurrentDomainFacade = $productOnCurrentDomainFacade;
        $this->productFilterFormTypeFactory = $productFilterFormTypeFactory;
        $this->productListOrderingModeForListFacade = $productListOrderingModeForListFacade;
        $this->productListOrderingModeForBrandFacade = $productListOrderingModeForBrandFacade;
        $this->productListOrderingModeForSearchFacade = $productListOrderingModeForSearchFacade;
        $this->moduleFacade = $moduleFacade;
        $this->brandFacade = $brandFacade;
    }

    /**
     * @param int $id
     */
    public function detailAction($id)
    {
        $productDetail = $this->productOnCurrentDomainFacade->getVisibleProductDetailById($id);
        $product = $productDetail->getProduct();

        if ($product->isVariant()) {
            return $this->redirectToRoute('front_product_detail', ['id' => $product->getMainVariant()->getId()]);
        }

        $accessoriesDetails = $this->productOnCurrentDomainFacade->getAccessoriesProductDetailsForProduct($product);
        $variantsDetails = $this->productOnCurrentDomainFacade->getVariantsProductDetailsForProduct($product);
        $productMainCategory = $this->categoryFacade->getProductMainCategoryByDomainId($product, $this->domain->getId());

        return $this->render('@ShopsysShop/Front/Content/Product/detail.html.twig', [
            'productDetail' => $productDetail,
            'accesoriesDetails' => $accessoriesDetails,
            'variantsDetails' => $variantsDetails,
            'productMainCategory' => $productMainCategory,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     */
    public function listByCategoryAction(Request $request, $id)
    {
        try {
            $page = $this->pagination->getPage($request);
        } catch (\Shopsys\ShopBundle\Component\Paginator\Exception\InvalidRequestedPageException $e) {
            return $this->redirectToRoute($e->getRedirectRoute(), $e->getRedirectParameters());
        }
        $category = $this->categoryFacade->getVisibleOnDomainById($this->domain->getId(), $id);

        $orderingMode = $this->productListOrderingModeForListFacade->getOrderingModeFromRequest(
            $request
        );

        $productFilterData = new ProductFilterData();

        $productFilterFormType = $this->createProductFilterFormTypeForCategory($category);
        $filterForm = $this->createForm($productFilterFormType, $productFilterData);
        $filterForm->handleRequest($request);

        $paginationResult = $this->productOnCurrentDomainFacade->getPaginatedProductDetailsInCategory(
            $productFilterData,
            $orderingMode,
            $page,
            self::PRODUCTS_PER_PAGE,
            $id
        );

        $productFilterCountData = null;
        if ($this->moduleFacade->isEnabled(ModuleList::PRODUCT_FILTER_COUNTS)) {
            $productFilterCountData = $this->productOnCurrentDomainFacade->getProductFilterCountDataInCategory(
                $id,
                $productFilterFormType,
                $productFilterData
            );
        }

        $viewParameters = [
            'paginationResult' => $paginationResult,
            'productFilterCountData' => $productFilterCountData,
            'category' => $category,
            'categoryDomain' => $category->getCategoryDomain($this->domain->getId()),
            'filterForm' => $filterForm->createView(),
            'filterFormSubmited' => $filterForm->isSubmitted(),
            'visibleChildren' => $this->categoryFacade->getAllVisibleChildrenByCategoryAndDomainId($category, $this->domain->getId()),
            'priceRange' => $productFilterFormType->getPriceRange(),
        ];

        if ($request->isXmlHttpRequest()) {
            return $this->render('@ShopsysShop/Front/Content/Product/ajaxList.html.twig', $viewParameters);
        } else {
            $viewParameters['POSITION_PRODUCT_LIST'] = AdvertPositionList::POSITION_PRODUCT_LIST;

            return $this->render('@ShopsysShop/Front/Content/Product/list.html.twig', $viewParameters);
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     */
    public function listByBrandAction(Request $request, $id)
    {
        try {
            $page = $this->pagination->getPage($request);
        } catch (\Shopsys\ShopBundle\Component\Paginator\Exception\InvalidRequestedPageException $e) {
            return $this->redirectToRoute($e->getRedirectRoute(), $e->getRedirectParameters());
        }

        $orderingMode = $this->productListOrderingModeForBrandFacade->getOrderingModeFromRequest(
            $request
        );

        $paginationResult = $this->productOnCurrentDomainFacade->getPaginatedProductDetailsForBrand(
            $orderingMode,
            $page,
            self::PRODUCTS_PER_PAGE,
            $id
        );

        $viewParameters = [
            'paginationResult' => $paginationResult,
            'brand' => $this->brandFacade->getById($id),
        ];

        if ($request->isXmlHttpRequest()) {
            return $this->render('@ShopsysShop/Front/Content/Product/ajaxListByBrand.html.twig', $viewParameters);
        } else {
            return $this->render('@ShopsysShop/Front/Content/Product/listByBrand.html.twig', $viewParameters);
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function searchAction(Request $request)
    {
        $searchText = $request->query->get(self::SEARCH_TEXT_PARAMETER);

        try {
            $page = $this->pagination->getPage($request);
        } catch (\Shopsys\ShopBundle\Component\Paginator\Exception\InvalidRequestedPageException $e) {
            return $this->redirectToRoute($e->getRedirectRoute(), $e->getRedirectParameters());
        }

        $orderingMode = $this->productListOrderingModeForSearchFacade->getOrderingModeFromRequest(
            $request
        );

        $productFilterData = new ProductFilterData();

        $productFilterFormType = $this->createProductFilterFormTypeForSearch($searchText);
        $filterForm = $this->createForm($productFilterFormType, $productFilterData);
        $filterForm->handleRequest($request);

        $paginationResult = $this->productOnCurrentDomainFacade->getPaginatedProductDetailsForSearch(
            $searchText,
            $productFilterData,
            $orderingMode,
            $page,
            self::PRODUCTS_PER_PAGE
        );

        $productFilterCountData = null;
        if ($this->moduleFacade->isEnabled(ModuleList::PRODUCT_FILTER_COUNTS)) {
            $productFilterCountData = $this->productOnCurrentDomainFacade->getProductFilterCountDataForSearch(
                $searchText,
                $productFilterFormType,
                $productFilterData
            );
        }

        $viewParameters = [
            'paginationResult' => $paginationResult,
            'productFilterCountData' => $productFilterCountData,
            'filterForm' => $filterForm->createView(),
            'filterFormSubmited' => $filterForm->isSubmitted(),
            'searchText' => $searchText,
            'SEARCH_TEXT_PARAMETER' => self::SEARCH_TEXT_PARAMETER,
            'priceRange' => $productFilterFormType->getPriceRange(),
        ];

        if ($request->isXmlHttpRequest()) {
            return $this->render('@ShopsysShop/Front/Content/Product/ajaxSearch.html.twig', $viewParameters);
        } else {
            $viewParameters['foundCategories'] = $this->searchCategories($searchText);
            return $this->render('@ShopsysShop/Front/Content/Product/search.html.twig', $viewParameters);
        }
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Category\Category $category
     * @return \Shopsys\ShopBundle\Form\Front\Product\ProductFilterFormType
     */
    private function createProductFilterFormTypeForCategory(Category $category)
    {
        return $this->productFilterFormTypeFactory->createForCategory(
            $this->domain->getId(),
            $this->domain->getLocale(),
            $category
        );
    }

    /**
     * @param string|null $searchText
     * @return \Shopsys\ShopBundle\Form\Front\Product\ProductFilterFormType
     */
    private function createProductFilterFormTypeForSearch($searchText)
    {
        return $this->productFilterFormTypeFactory->createForSearch(
            $this->domain->getId(),
            $this->domain->getLocale(),
            $searchText
        );
    }

    /**
     * @param string|null $searchText
     * @return \Shopsys\ShopBundle\Model\Category\Category[]
     */
    private function searchCategories($searchText)
    {
        return $this->categoryFacade->getVisibleByDomainAndSearchText(
            $this->domain->getId(),
            $this->domain->getLocale(),
            $searchText
        );
    }

    public function selectOrderingModeForListAction(Request $request)
    {
        $productListOrderingConfig = $this->productListOrderingModeForListFacade->getProductListOrderingConfig();

        $orderingMode = $this->productListOrderingModeForListFacade->getOrderingModeFromRequest(
            $request
        );

        return $this->render('@ShopsysShop/Front/Content/Product/orderingSetting.html.twig', [
            'orderingModesNames' => $productListOrderingConfig->getSupportedOrderingModesNames(),
            'activeOrderingMode' => $orderingMode,
            'cookieName' => $productListOrderingConfig->getCookieName(),
        ]);
    }

    public function selectOrderingModeForListByBrandAction(Request $request)
    {
        $productListOrderingConfig = $this->productListOrderingModeForBrandFacade->getProductListOrderingConfig();

        $orderingMode = $this->productListOrderingModeForBrandFacade->getOrderingModeFromRequest(
            $request
        );

        return $this->render('@ShopsysShop/Front/Content/Product/orderingSetting.html.twig', [
            'orderingModesNames' => $productListOrderingConfig->getSupportedOrderingModesNames(),
            'activeOrderingMode' => $orderingMode,
            'cookieName' => $productListOrderingConfig->getCookieName(),
        ]);
    }

    public function selectOrderingModeForSearchAction(Request $request)
    {
        $productListOrderingConfig = $this->productListOrderingModeForSearchFacade->getProductListOrderingConfig();

        $orderingMode = $this->productListOrderingModeForSearchFacade->getOrderingModeFromRequest(
            $request
        );

        return $this->render('@ShopsysShop/Front/Content/Product/orderingSetting.html.twig', [
            'orderingModesNames' => $productListOrderingConfig->getSupportedOrderingModesNames(),
            'activeOrderingMode' => $orderingMode,
            'cookieName' => $productListOrderingConfig->getCookieName(),
        ]);
    }
}
