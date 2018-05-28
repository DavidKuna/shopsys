<?php

namespace Shopsys\FrameworkBundle\Model\Product\Brand\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BrandDomainNotFoundException extends NotFoundHttpException implements BrandException
{
}
