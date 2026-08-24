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

    /**
     * Cartão sandbox Mastercard para fluxos 3DS 2.2.
     *
     * @see https://docs.cielo.com.br/ecommerce-cielo/docs/autorizacao-autenticacao
     */
    private const SANDBOX_MASTERCARD_3DS_NUMBER = '5502095822650000';

    private const THREEDS22_CAVV = 'AAABB2gHA1B5EFNjWQcDAAAAAAB=';

    private const THREEDS22_XID = 'Uk5ZanBHcWw2RjRCbEN5dGtiMTB=';

    private const THREEDS22_ECI = '5';

    private const THREEDS22_VERSION = '2.2.0';

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

    public function testAuthorizeCreditCardPaymentWith3ds22Authentication(): void
    {
        $dateExpiration = new \DateTimeImmutable('+5 years');
        $sale = new Sale($this->uniqueMerchantOrderId('3DS22'));
        $sale->customer('Comprador E2E 3DS 2.2')
            ->setIdentity('12345678909')
            ->setIdentityType('CPF');

        $payment = $sale->payment(self::CREDIT_CARD_AMOUNT);
        $payment->setCapture(true)
            ->setAuthenticate(true)
            ->creditCard('123', CreditCard::MASTERCARD)
            ->setExpirationDate($dateExpiration->format('m/Y'))
            ->setCardNumber(self::SANDBOX_MASTERCARD_3DS_NUMBER)
            ->setHolder('Comprador E2E 3DS 2.2');

        $payment->externalAuthentication(
            cavv: self::THREEDS22_CAVV,
            eci: self::THREEDS22_ECI,
            version: self::THREEDS22_VERSION,
        )->setXid(self::THREEDS22_XID);

        $created = $this->getCieloEcommerce()->createSale($sale);
        $payment = $created->getPayment();

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertNotEmpty($payment->getPaymentId());
        $this->assertSame(self::CREDIT_CARD_AMOUNT, (int) $payment->getAmount());
        $this->assertSame(TransactionStatus::PAYMENT_CONFIRMED, (int) $payment->getStatus());
    }

    public function testAuthorizeCreditCardPaymentWith3ds22DataOnlyAuthentication(): void
    {
        $dateExpiration = new \DateTimeImmutable('+5 years');
        $sale = new Sale($this->uniqueMerchantOrderId('3DS22DO'));
        $sale->customer('Comprador E2E 3DS 2.2 Data Only')
            ->setIdentity('12345678909')
            ->setIdentityType('CPF');

        $payment = $sale->payment(self::CREDIT_CARD_AMOUNT);
        $payment->setCapture(true)
            ->creditCard('123', CreditCard::MASTERCARD)
            ->setExpirationDate($dateExpiration->format('m/Y'))
            ->setCardNumber(self::SANDBOX_MASTERCARD_3DS_NUMBER)
            ->setHolder('Comprador E2E 3DS 2.2 Data Only');

        $payment->externalAuthentication(
            cavv: self::THREEDS22_CAVV,
            eci: self::THREEDS22_ECI,
            dataOnly: true,
            version: self::THREEDS22_VERSION,
        )->setXid(self::THREEDS22_XID);

        $created = $this->getCieloEcommerce()->createSale($sale);
        $payment = $created->getPayment();

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertNotEmpty($payment->getPaymentId());
        $this->assertSame(self::CREDIT_CARD_AMOUNT, (int) $payment->getAmount());
        $this->assertSame(TransactionStatus::PAYMENT_CONFIRMED, (int) $payment->getStatus());
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
