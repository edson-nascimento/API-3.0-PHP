<?php

namespace TestApp\E2E;

use Cielo\API30\Ecommerce\CreditCard;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\TransactionStatus;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;

#[Group('payment')]
final class PaymentIntegrationTest extends AbstractE2ETestCase
{
    private const CREDIT_CARD_AMOUNT = 15700;

    private const PIX_AMOUNT = 1000;

    /**
     * Cartão sandbox com final 1: autorização aprovada.
     *
     * @see https://docs.cielo.com.br/ecommerce-cielo/reference/credito-sandbox
     */
    private const SANDBOX_VISA_NUMBER = '4024007153763191';

    public function testAuthorizeCreditCardPayment(): Sale
    {
        $dateExpiration = new \DateTimeImmutable('+5 years');
        $sale = new Sale($this->uniqueMerchantOrderId('CC'));
        $sale->customer('Comprador E2E Credito')
            ->setIdentity('12345678909')
            ->setIdentityType('CPF');
        $sale->payment(self::CREDIT_CARD_AMOUNT)
            ->setCapture(false)
            ->creditCard('123', CreditCard::VISA)
            ->setExpirationDate($dateExpiration->format('m/Y'))
            ->setCardNumber(self::SANDBOX_VISA_NUMBER)
            ->setHolder('Comprador E2E Credito');

        $created = $this->getCieloEcommerce()->createSale($sale);
        $payment = $created->getPayment();

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertNotEmpty($payment->getPaymentId());
        $this->assertSame(self::CREDIT_CARD_AMOUNT, (int) $payment->getAmount());
        $this->assertSame(TransactionStatus::AUTHORIZED, (int) $payment->getStatus());

        return $created;
    }

    #[Depends('testAuthorizeCreditCardPayment')]
    public function testCaptureCreditCardPayment(Sale $sale): Payment
    {
        $authorized = $sale->getPayment();
        $this->assertInstanceOf(Payment::class, $authorized);

        $paymentId = (string) $authorized->getPaymentId();
        $amount = (int) $authorized->getAmount();

        $this->assertSame(self::CREDIT_CARD_AMOUNT, $amount);

        $captured = $this->getCieloEcommerce()->captureSale($paymentId, $amount);

        $this->assertSame(TransactionStatus::PAYMENT_CONFIRMED, (int) $captured->getStatus());
        $this->assertReturnedAmountMatches($amount, $captured);

        $queried = $this->getCieloEcommerce()->getSale($paymentId)->getPayment();
        $this->assertInstanceOf(Payment::class, $queried);
        $this->assertSame($amount, (int) $queried->getAmount());
        $this->assertReturnedAmountMatches($amount, $queried);

        return $captured;
    }

    #[Depends('testAuthorizeCreditCardPayment')]
    #[Depends('testCaptureCreditCardPayment')]
    public function testCancelCreditCardPayment(Sale $sale, Payment $capturedPayment): void
    {
        $this->assertSame(TransactionStatus::PAYMENT_CONFIRMED, (int) $capturedPayment->getStatus());

        $authorized = $sale->getPayment();
        $this->assertInstanceOf(Payment::class, $authorized);

        $paymentId = (string) $authorized->getPaymentId();
        $amount = (int) $authorized->getAmount();

        $this->assertSame(self::CREDIT_CARD_AMOUNT, $amount);

        $cancelled = $this->getCieloEcommerce()->cancelSale($paymentId, $amount);

        $this->assertContains((int) $cancelled->getStatus(), [TransactionStatus::VOIDED, TransactionStatus::REFUNDED]);
        $this->assertReturnedAmountMatches($amount, $cancelled);

        $queried = $this->getCieloEcommerce()->getSale($paymentId)->getPayment();
        $this->assertInstanceOf(Payment::class, $queried);
        $this->assertSame($amount, (int) $queried->getAmount());
        $this->assertReturnedAmountMatches($amount, $queried);
    }

    public function testCreatePixQrCodePayment(): void
    {
        $sale = new Sale($this->uniqueMerchantOrderId('PIX'));
        $sale->customer('Comprador Pix Teste')
            ->setIdentity('12345678909')
            ->setIdentityType('CPF');
        $sale->payment(self::PIX_AMOUNT)->pix();

        $created = $this->getCieloEcommerce()->createSale($sale);
        $payment = $created->getPayment();

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertNotEmpty($payment->getPaymentId());
        $this->assertSame(Payment::PAYMENTTYPE_PIX, $payment->getType());
        $this->assertSame(self::PIX_AMOUNT, (int) $payment->getAmount());
        $this->assertNotEmpty($payment->getQrCodeString());
        $this->assertSame(TransactionStatus::PENDING, (int) $payment->getStatus());
    }

    private function assertReturnedAmountMatches(int $sentAmount, Payment $payment): void
    {
        if ($payment->getAmount() !== null) {
            $this->assertSame($sentAmount, (int) $payment->getAmount());
        }

        if ($payment->getCapturedAmount() !== null) {
            $this->assertSame($sentAmount, (int) $payment->getCapturedAmount());
        }

        if ($payment->getVoidedAmount() !== null) {
            $this->assertSame($sentAmount, (int) $payment->getVoidedAmount());
        }
    }
}
