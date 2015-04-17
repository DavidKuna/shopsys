<?php

namespace SS6\ShopBundle\Tests\Unit\Model\Pricing\Vat;

use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Model\Pricing\Vat\Vat;
use SS6\ShopBundle\Model\Pricing\Vat\VatData;
use SS6\ShopBundle\Model\Pricing\Vat\VatService;
use SS6\ShopBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;

class VatServiceTest extends PHPUnit_Framework_TestCase {

	public function testCreate() {
		$productPriceRecalculationSchedulerMock = $this->getMockBuilder(ProductPriceRecalculationScheduler::class)
			->disableOriginalConstructor()
			->getMock();

		$vatService = new VatService($productPriceRecalculationSchedulerMock);

		$vatDataOriginal = new VatData('vatName', '21.00');
		$vat = $vatService->create($vatDataOriginal);

		$vatDataNew = new VatData();
		$vatDataNew->setFromEntity($vat);

		$this->assertEquals($vatDataOriginal, $vatDataNew);
	}

	public function testEdit() {
		$productPriceRecalculationSchedulerMock = $this->getMockBuilder(ProductPriceRecalculationScheduler::class)
			->disableOriginalConstructor()
			->getMock();

		$vatService = new VatService($productPriceRecalculationSchedulerMock);

		$vatDataOld = new VatData('oldVatName', '21.00');
		$vatDataEdit = new VatData('editVatName', '15.00');
		$vat = new Vat($vatDataOld);

		$vatService->edit($vat, $vatDataEdit);

		$vatDataNew = new VatData();
		$vatDataNew->setFromEntity($vat);

		$this->assertEquals($vatDataEdit, $vatDataNew);
	}

	public function testEditSchedulesPriceRecalculation() {
		$productPriceRecalculationSchedulerMock = $this->getMockBuilder(ProductPriceRecalculationScheduler::class)
			->disableOriginalConstructor()
			->getMock();
		$productPriceRecalculationSchedulerMock->expects($this->once())->method('scheduleRecalculatePriceForVat');

		$vatService = new VatService($productPriceRecalculationSchedulerMock);

		$vatDataOld = new VatData('oldVatName', '21.00');
		$vatDataEdit = new VatData('editVatName', '15.00');
		$vat = new Vat($vatDataOld);

		$vatService->edit($vat, $vatDataEdit);
	}

}