<?php

namespace TestApp\Unit;

use Cielo\API30\Ecommerce\CreditCard;
use Cielo\API30\Ecommerce\ExternalAuthentication;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\Sale;
use PHPUnit\Framework\TestCase;

final class ExternalAuthenticationTest extends TestCase
{
    public function testFluentSettersAndGetters(): void
    {
        $external = new ExternalAuthentication();

        $result = $external->setCavv('AAABB...')
            ->setXid('Uk5Z...')
            ->setEci('5')
            ->setVersion('2.2.0')
            ->setReferenceId('a24a5d87-b1a1-4aef-a37b-2f30b91274e6')
            ->setDataOnly(false);

        $this->assertSame($external, $result);
        $this->assertSame('AAABB...', $external->getCavv());
        $this->assertSame('Uk5Z...', $external->getXid());
        $this->assertSame('5', $external->getEci());
        $this->assertSame('2.2.0', $external->getVersion());
        $this->assertSame('a24a5d87-b1a1-4aef-a37b-2f30b91274e6', $external->getReferenceId());
        $this->assertFalse($external->getDataOnly());
    }

    public function testPopulateFromPascalCaseKeys(): void
    {
        $data = new \stdClass();
        $data->Cavv = 'AAABB...';
        $data->Xid = 'Uk5Z...';
        $data->Eci = '5';
        $data->Version = '2.2.0';
        $data->ReferenceId = 'a24a5d87-b1a1-4aef-a37b-2f30b91274e6';
        $data->DataOnly = true;

        $external = new ExternalAuthentication();
        $external->populate($data);

        $this->assertSame('AAABB...', $external->getCavv());
        $this->assertSame('Uk5Z...', $external->getXid());
        $this->assertSame('5', $external->getEci());
        $this->assertSame('2.2.0', $external->getVersion());
        $this->assertSame('a24a5d87-b1a1-4aef-a37b-2f30b91274e6', $external->getReferenceId());
        $this->assertTrue($external->getDataOnly());
    }

    public function testPopulateReferenceIdFallback(): void
    {
        $data = new \stdClass();
        $data->ReferenceID = 'fallback-guid';

        $external = new ExternalAuthentication();
        $external->populate($data);

        $this->assertSame('fallback-guid', $external->getReferenceId());
    }

    public function testDataOnlyDefaultsToNull(): void
    {
        $external = new ExternalAuthentication();
        $external->populate(new \stdClass());

        $this->assertNull($external->getDataOnly());
    }

    public function testFromJson(): void
    {
        $json = json_encode([
            'Cavv' => 'AAABB...',
            'Eci' => '5',
            'Version' => '2.2.0',
        ]);

        $external = ExternalAuthentication::fromJson($json);

        $this->assertSame('AAABB...', $external->getCavv());
        $this->assertSame('5', $external->getEci());
        $this->assertSame('2.2.0', $external->getVersion());
    }

    public function testPaymentExternalAuthentication(): void
    {
        $sale = new Sale('123');
        $payment = $sale->payment(15700);
        $payment->setAuthenticate(true);
        $payment->creditCard('123', CreditCard::VISA);

        $external = $payment->externalAuthentication('AAABB...', '5', '2.2.0')
            ->setXid('Uk5Z...')
            ->setReferenceId('a24a5d87-b1a1-4aef-a37b-2f30b91274e6');

        $this->assertSame($external, $payment->getExternalAuthentication());
        $this->assertTrue($payment->getAuthenticate());
        $this->assertSame('AAABB...', $external->getCavv());
        $this->assertSame('5', $external->getEci());
        $this->assertSame('2.2.0', $external->getVersion());

        $serialized = $payment->jsonSerialize();
        $this->assertArrayHasKey('externalAuthentication', $serialized);
        $this->assertSame($external, $serialized['externalAuthentication']);
    }

    public function testPaymentDataOnlyAuthentication(): void
    {
        $sale = new Sale('123');
        $payment = $sale->payment(15700);
        $payment->creditCard('123', CreditCard::MASTERCARD);

        $external = $payment->dataOnlyAuthentication('4', '2.2.0')
            ->setReferenceId('a24a5d87-b1a1-4aef-a37b-2f30b91274e6');

        $this->assertFalse($payment->getAuthenticate());
        $this->assertTrue($external->getDataOnly());
        $this->assertSame('4', $external->getEci());
        $this->assertSame('2.2.0', $external->getVersion());
        $this->assertNull($external->getCavv());
        $this->assertSame($external, $payment->getExternalAuthentication());
    }
}
