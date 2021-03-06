<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

class TransportData
{
    /**
     * @var string[]
     */
    public $name;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public $vat;

    /**
     * @var string[]
     */
    public $description;

    /**
     * @var string[]
     */
    public $instructions;

    /**
     * @var bool
     */
    public $hidden;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData
     */
    public $image;

    /**
     * @var int[]
     */
    public $domains;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment[]
     */
    public $payments;

    /**
     * @var string[]
     */
    public $pricesByCurrencyId;

    /**
     * @param string[] $names
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat|null $vat
     * @param string[] $descriptions
     * @param string[] $instructions
     * @param bool $hidden
     * @param int[] $domains
     * @param string[] $pricesByCurrencyId
     */
    public function __construct(
        array $names = [],
        Vat $vat = null,
        array $descriptions = [],
        array $instructions = [],
        $hidden = false,
        array $domains = [],
        array $pricesByCurrencyId = []
    ) {
        $this->name = $names;
        $this->vat = $vat;
        $this->description = $descriptions;
        $this->instructions = $instructions;
        $this->hidden = $hidden;
        $this->image = new ImageUploadData();
        $this->domains = $domains;
        $this->pricesByCurrencyId = $pricesByCurrencyId;
        $this->payments = [];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportDomain[] $transportDomains
     */
    public function setFromEntity(Transport $transport, array $transportDomains)
    {
        $translations = $transport->getTranslations();
        $names = [];
        $descriptions = [];
        $instructions = [];
        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
            $descriptions[$translate->getLocale()] = $translate->getDescription();
            $instructions[$translate->getLocale()] = $translate->getInstructions();
        }
        $this->name = $names;
        $this->description = $descriptions;
        $this->instructions = $instructions;
        $this->hidden = $transport->isHidden();
        $this->vat = $transport->getVat();

        $domains = [];
        foreach ($transportDomains as $transportDomain) {
            $domains[] = $transportDomain->getDomainId();
        }
        $this->domains = $domains;
        $this->payments = $transport->getPayments()->toArray();
    }
}
