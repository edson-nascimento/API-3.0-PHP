<?php

namespace TestApp\Unit;

use Cielo\API30\Ecommerce\CreditCard;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\Sale;
use PHPUnit\Framework\TestCase;

final class SaleTest extends TestCase
{
    public function testSalePayment(): void
    {
        $sale = new Sale('123');

        $this->assertSame('123', $sale->getMerchantOrderId());

        $payment = $sale->payment(15800);
        $this->assertSame($payment, $sale->getPayment());
        $this->assertSame(15800, $payment->getAmount());
        $this->assertSame(1, $payment->getInstallments());

        $payment = $sale->payment(15800, 7);
        $this->assertSame(7, $payment->getInstallments());
    }

    public function testSalePaymentType(): void
    {
        $sale = new Sale('123');
        $payment = $sale->payment(27400);

        $payment->creditCard('123', CreditCard::VISA)
            ->setExpirationDate('12/2018')
            ->setCardNumber('0000000000000001')
            ->setHolder('Fulano de Tal')
            ->setSaveCard(true);
        $this->assertSame(Payment::PAYMENTTYPE_CREDITCARD, $payment->getType());
        $this->assertFalse($payment->getCapture());

        $payment->debitCard('123', CreditCard::VISA)
            ->setExpirationDate('12/2018')
            ->setCardNumber('0000000000000001')
            ->setHolder('Fulano de Tal')
            ->setSaveCard(true);
        $this->assertSame(Payment::PAYMENTTYPE_DEBITCARD, $payment->getType());
        $this->assertFalse($payment->getCapture());

        $payment->pix();
        $this->assertSame(Payment::PAYMENTTYPE_PIX, $payment->getType());
        $this->assertTrue($payment->getCapture());
    }
}
