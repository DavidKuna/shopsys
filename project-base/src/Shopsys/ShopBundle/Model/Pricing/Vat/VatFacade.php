<?php

namespace Shopsys\ShopBundle\Model\Pricing\Vat;

use Doctrine\ORM\EntityManager;
use Shopsys\ShopBundle\Component\Setting\Setting;
use Shopsys\ShopBundle\Model\Pricing\Vat\VatData;
use Shopsys\ShopBundle\Model\Pricing\Vat\VatRepository;
use Shopsys\ShopBundle\Model\Pricing\Vat\VatService;
use Shopsys\ShopBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;

class VatFacade
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Shopsys\ShopBundle\Model\Pricing\Vat\VatRepository
     */
    private $vatRepository;

    /**
     * @var \Shopsys\ShopBundle\Model\Pricing\Vat\VatService
     */
    private $vatService;

    /**
     * @var \Shopsys\ShopBundle\Component\Setting\Setting
     */
    private $setting;

    /**
     * @var \Shopsys\ShopBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler
     */
    private $productPriceRecalculationScheduler;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @param \Shopsys\ShopBundle\Model\Pricing\Vat\VatRepository $vatRepository
     * @param \Shopsys\ShopBundle\Model\Pricing\Vat\VatService $vatService
     * @param \Shopsys\ShopBundle\Component\Setting\Setting $setting
     * @param \Shopsys\ShopBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler $productPriceRecalculationScheduler
     */
    public function __construct(
        EntityManager $em,
        VatRepository $vatRepository,
        VatService $vatService,
        Setting $setting,
        ProductPriceRecalculationScheduler $productPriceRecalculationScheduler
    ) {
        $this->em = $em;
        $this->vatRepository = $vatRepository;
        $this->vatService = $vatService;
        $this->setting = $setting;
        $this->productPriceRecalculationScheduler = $productPriceRecalculationScheduler;
    }

    /**
     * @param int $vatId
     * @return \Shopsys\ShopBundle\Model\Pricing\Vat\Vat
     */
    public function getById($vatId)
    {
        return $this->vatRepository->getById($vatId);
    }

    /**
     * @return \Shopsys\ShopBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAll()
    {
        return $this->vatRepository->getAll();
    }

    /**
     * @return \Shopsys\ShopBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAllIncludingMarkedForDeletion()
    {
        return $this->vatRepository->getAllIncludingMarkedForDeletion();
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Pricing\Vat\VatData $vatData
     * @return \Shopsys\ShopBundle\Model\Pricing\Vat\Vat
     */
    public function create(VatData $vatData)
    {
        $vat = $this->vatService->create($vatData);
        $this->em->persist($vat);
        $this->em->flush();

        return $vat;
    }

    /**
     * @param int $vatId
     * @param \Shopsys\ShopBundle\Model\Pricing\Vat\VatData $vatData
     * @return \Shopsys\ShopBundle\Model\Pricing\Vat\Vat
     */
    public function edit($vatId, VatData $vatData)
    {
        $vat = $this->vatRepository->getById($vatId);
        $this->vatService->edit($vat, $vatData);
        $this->em->flush();

        $this->productPriceRecalculationScheduler->scheduleAllProductsForDelayedRecalculation();

        return $vat;
    }

    /**
     * @param int $vatId
     * @param int|null $newVatId
     */
    public function deleteById($vatId, $newVatId = null)
    {
        $oldVat = $this->vatRepository->getById($vatId);
        $newVat = $newVatId ? $this->vatRepository->getById($newVatId) : null;

        if ($oldVat->isMarkedAsDeleted()) {
            throw new \Shopsys\ShopBundle\Model\Pricing\Vat\Exception\VatMarkedAsDeletedDeleteException();
        }

        if ($this->vatRepository->existsVatToBeReplacedWith($oldVat)) {
            throw new \Shopsys\ShopBundle\Model\Pricing\Vat\Exception\VatWithReplacedDeleteException();
        }

        if ($newVat !== null) {
            $newDefaultVat = $this->vatService->getNewDefaultVat(
                $this->getDefaultVat(),
                $oldVat,
                $newVat
            );
            $this->setDefaultVat($newDefaultVat);

            $this->vatRepository->replaceVat($oldVat, $newVat);
            $oldVat->markForDeletion($newVat);
        } else {
            $this->em->remove($oldVat);
        }

        $this->em->flush();
    }

    /**
     * @return int
     */
    public function deleteAllReplacedVats()
    {
        $vatsForDelete = $this->vatRepository->getVatsWithoutProductsMarkedForDeletion();
        foreach ($vatsForDelete as $vatForDelete) {
            $this->em->remove($vatForDelete);
        }
        $this->em->flush($vatsForDelete);

        return count($vatsForDelete);
    }

    /**
     * @return \Shopsys\ShopBundle\Model\Pricing\Vat\Vat
     */
    public function getDefaultVat()
    {
        $defaultVatId = $this->setting->get(Vat::SETTING_DEFAULT_VAT);

        return $this->vatRepository->getById($defaultVatId);
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Pricing\Vat\Vat $vat
     */
    public function setDefaultVat(Vat $vat)
    {
        $this->setting->set(Vat::SETTING_DEFAULT_VAT, $vat->getId());
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Pricing\Vat\Vat $vat
     * @return bool
     */
    public function isVatUsed(Vat $vat)
    {
        $defaultVat = $this->getDefaultVat();

        return $defaultVat === $vat || $this->vatRepository->isVatUsed($vat);
    }

    /**
     * @param int $vatId
     * @return \Shopsys\ShopBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAllExceptId($vatId)
    {
        return $this->vatRepository->getAllExceptId($vatId);
    }

    /**
     * @param int $percent
     * @return \Shopsys\ShopBundle\Model\Pricing\Vat\Vat
     */
    public function getVatByPercent($percent)
    {
        return $this->vatRepository->getVatByPercent($percent);
    }
}
