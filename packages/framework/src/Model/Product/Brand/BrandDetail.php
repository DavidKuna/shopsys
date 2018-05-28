<?php

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

class BrandDetail
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    public $brand;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     */
    public function __construct(
        Brand $brand
    ) {
        $this->brand = $brand;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }
}
