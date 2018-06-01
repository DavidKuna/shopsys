<?php

namespace Tests\ShopBundle\Database\Model\Order;

use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Tests\ShopBundle\Test\DatabaseTestCase;

class OrderTransportAndPaymentTest extends DatabaseTestCase
{
    public function testVisibleTransport()
    {
        $em = $this->getEntityManager();

        $domainId = 1;
        $vat = new Vat(new VatData('vat', 21));
        $transport = new Transport(new TransportData(['cs' => 'transportName', 'en' => 'transportName'], $vat, [], [], false, [$domainId]));
        $payment = new Payment(new PaymentData(['cs' => 'paymentName', 'en' => 'paymentName'], $vat, [], [], false, [$domainId]));

        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->flush();
        $em->persist($payment);
        $em->flush();

        $transportFacade = $this->getContainer()->get(TransportFacade::class);
        /* @var $transportFacade \Shopsys\FrameworkBundle\Model\Transport\TransportFacade */
        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();
        $visibleTransports = $transportFacade->getVisibleOnCurrentDomain($visiblePayments);

        $this->assertContains($transport, $visibleTransports);
    }

    public function testVisibleTransportHiddenTransport()
    {
        $em = $this->getEntityManager();
        $domainId = 1;
        $vat = new Vat(new VatData('vat', 21));
        $transport = new Transport(new TransportData(['cs' => 'transportName', 'en' => 'transportName'], $vat, [], [], true, [$domainId]));
        $payment = new Payment(new PaymentData(['cs' => 'paymentName', 'en' => 'paymentName'], $vat, [], [], false, [$domainId]));

        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->flush();
        $em->persist($payment);
        $em->flush();

        $transportFacade = $this->getContainer()->get(TransportFacade::class);
        /* @var $transportFacade \Shopsys\FrameworkBundle\Model\Transport\TransportFacade */
        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();
        $visibleTransports = $transportFacade->getVisibleOnCurrentDomain($visiblePayments);

        $this->assertNotContains($transport, $visibleTransports);
    }

    public function testVisibleTransportHiddenPayment()
    {
        $em = $this->getEntityManager();

        $domainId = 1;

        $vat = new Vat(new VatData('vat', 21));
        $transport = new Transport(new TransportData(['cs' => 'transportName', 'en' => 'transportName'], $vat, [], [], false, [$domainId]));
        $payment = new Payment(new PaymentData(['cs' => 'paymentName', 'en' => 'paymentName'], $vat, [], [], true));

        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->flush();
        $em->persist($payment);
        $em->flush();

        $transportFacade = $this->getContainer()->get(TransportFacade::class);
        /* @var $transportFacade \Shopsys\FrameworkBundle\Model\Transport\TransportFacade */
        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();
        $visibleTransports = $transportFacade->getVisibleOnCurrentDomain($visiblePayments);

        $this->assertNotContains($transport, $visibleTransports);
    }

    public function testVisibleTransportNoPayment()
    {
        $em = $this->getEntityManager();

        $domainId = 1;

        $vat = new Vat(new VatData('vat', 21));
        $transport = new Transport(new TransportData(['cs' => 'transportName', 'en' => 'transportName'], $vat, [], [], false, [$domainId]));

        $em->persist($vat);
        $em->persist($transport);
        $em->flush();

        $transportFacade = $this->getContainer()->get(TransportFacade::class);
        /* @var $transportFacade \Shopsys\FrameworkBundle\Model\Transport\TransportFacade */
        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();
        $visibleTransports = $transportFacade->getVisibleOnCurrentDomain($visiblePayments);

        $this->assertNotContains($transport, $visibleTransports);
    }

    public function testVisibleTransportOnDifferentDomain()
    {
        $em = $this->getEntityManager();

        $firstDomainId = 1;
        $domainId = 2;
        $vat = new Vat(new VatData('vat', 21));
        $transport = new Transport(new TransportData(['cs' => 'transportName', 'en' => 'transportName'], $vat, [], [], false, [$domainId]));
        $payment = new Payment(new PaymentData(['cs' => 'paymentName', 'en' => 'paymentName'], $vat, [], [], false, [$firstDomainId]));

        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->flush();
        $em->persist($payment);
        $em->flush();

        $transportFacade = $this->getContainer()->get(TransportFacade::class);
        /* @var $transportFacade \Shopsys\FrameworkBundle\Model\Transport\TransportFacade */
        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();
        $visibleTransports = $transportFacade->getVisibleOnCurrentDomain($visiblePayments);

        $this->assertNotContains($transport, $visibleTransports);
    }

    public function testVisibleTransportPaymentOnDifferentDomain()
    {
        $em = $this->getEntityManager();

        $firstDomainId = 1;
        $secondDomainId = 2;
        $vat = new Vat(new VatData('vat', 21));
        $transport = new Transport(new TransportData(['cs' => 'transportName', 'en' => 'transportName'], $vat, [], [], false, [$firstDomainId]));
        $payment = new Payment(new PaymentData(['cs' => 'paymentName', 'en' => 'paymentName'], $vat, [], [], false, [$secondDomainId]));
        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->flush();
        $em->persist($payment);
        $em->flush();

        $transportFacade = $this->getContainer()->get(TransportFacade::class);
        /* @var $transportFacade \Shopsys\FrameworkBundle\Model\Transport\TransportFacade */
        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();
        $visibleTransports = $transportFacade->getVisibleOnCurrentDomain($visiblePayments);

        $this->assertNotContains($transport, $visibleTransports);
    }

    public function testVisiblePayment()
    {
        $em = $this->getEntityManager();

        $domainId = 1;
        $vat = new Vat(new VatData('vat', 21));
        $transport = new Transport(new TransportData(['cs' => 'transportName', 'en' => 'transportName'], $vat, [], [], false, [$domainId]));
        $payment = new Payment(new PaymentData(['cs' => 'paymentName', 'en' => 'paymentName'], $vat, [], [], false, [$domainId]));

        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->flush();
        $em->persist($payment);
        $em->flush();

        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();

        $this->assertContains($payment, $visiblePayments);
    }

    public function testVisiblePaymentHiddenTransport()
    {
        $em = $this->getEntityManager();

        $domainId = 1;
        $vat = new Vat(new VatData('vat', 21));
        $transport = new Transport(new TransportData(['cs' => 'transportName', 'en' => 'transportName'], $vat, [], [], true, [$domainId]));
        $payment = new Payment(new PaymentData(['cs' => 'paymentName', 'en' => 'paymentName'], $vat, [], [], false, [$domainId]));

        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->flush();
        $em->persist($payment);
        $em->flush();

        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();

        $this->assertNotContains($payment, $visiblePayments);
    }

    public function testVisiblePaymentHiddenPayment()
    {
        $em = $this->getEntityManager();

        $domainId = 1;
        $vat = new Vat(new VatData('vat', 21));
        $transport = new Transport(new TransportData(['cs' => 'transportName', 'en' => 'transportName'], $vat, [], [], false, [$domainId]));
        $payment = new Payment(new PaymentData(['cs' => 'paymentName', 'en' => 'paymentName'], $vat, [], [], true, [$domainId]));

        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->flush();
        $em->persist($payment);
        $em->flush();

        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();

        $this->assertNotContains($payment, $visiblePayments);
    }

    public function testVisiblePaymentNoTransport()
    {
        $em = $this->getEntityManager();

        $domainId = 1;
        $vat = new Vat(new VatData('vat', 21));
        $payment = new Payment(new PaymentData(['cs' => 'paymentName', 'en' => 'paymentName'], $vat, [], [], false, [$domainId]));

        $em->persist($vat);
        $em->flush();
        $em->persist($payment);
        $em->flush();

        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();

        $this->assertNotContains($payment, $visiblePayments);
    }

    public function testVisiblePaymentOnDifferentDomain()
    {
        $em = $this->getEntityManager();

        $firstDomainId = 1;
        $secondDomainId = 2;
        $vat = new Vat(new VatData('vat', 21));
        $transport = new Transport(new TransportData(['cs' => 'transportName', 'en' => 'transportName'], $vat, [], [], false, [$firstDomainId]));
        $payment = new Payment(new PaymentData(['cs' => 'paymentName', 'en' => 'paymentName'], $vat, [], [], false, [$secondDomainId]));
        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->flush();
        $em->persist($payment);
        $em->flush();

        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();

        $this->assertNotContains($payment, $visiblePayments);
    }

    public function testVisiblePaymentTransportOnDifferentDomain()
    {
        $em = $this->getEntityManager();

        $firstDomainId = 1;
        $secondDomainId = 2;
        $vat = new Vat(new VatData('vat', 21));
        $transport = new Transport(new TransportData(['cs' => 'transportName', 'en' => 'transportName'], $vat, [], [], false, [$firstDomainId]));
        $payment = new Payment(new PaymentData(['cs' => 'paymentName', 'en' => 'paymentName'], $vat, [], [], false, [$secondDomainId]));

        $payment->addTransport($transport);

        $em->persist($vat);
        $em->persist($transport);
        $em->persist($payment);
        $em->flush();

        $paymentFacade = $this->getContainer()->get(PaymentFacade::class);
        /* @var $paymentFacade \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade */

        $visiblePayments = $paymentFacade->getVisibleOnCurrentDomain();

        $this->assertNotContains($payment, $visiblePayments);
    }
}
