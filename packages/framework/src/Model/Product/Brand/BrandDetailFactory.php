<?php

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

class BrandDetailFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\BrandDetail
     */
    public function getDetailForBrand(Brand $brand)
    {
        return new BrandDetail($brand);
    }
}
