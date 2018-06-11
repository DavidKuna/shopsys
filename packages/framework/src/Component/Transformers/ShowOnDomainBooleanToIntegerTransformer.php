<?php

namespace Shopsys\FrameworkBundle\Component\Transformers;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\Form\DataTransformerInterface;

class ShowOnDomainBooleanToIntegerTransformer implements DataTransformerInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @param array $values
     * @return array
     */
    public function transform($values)
    {
        $enabledDomains = [];
        foreach ($values as $domainId => $showOnDomain) {
            if ($showOnDomain) {
                $enabledDomains[] = $domainId;
            }
        }

        return $enabledDomains;
    }

    /**
     * @param array $values
     * @return mixed
     */
    public function reverseTransform($values)
    {
        $domainIds = $this->domain->getAllIds();
        $enabledForDomains = [];

        foreach ($domainIds as $domainId) {
            $enabledForDomains[$domainId] = in_array($domainId, $values, true);
        }

        return $enabledForDomains;
    }
}
