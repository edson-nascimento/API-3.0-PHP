<?php

namespace TestApp\Unit;

use Cielo\API30\Ecommerce\TransactionStatus;
use PHPUnit\Framework\TestCase;

final class TransactionStatusTest extends TestCase
{
    /**
     * @see https://docs.cielo.com.br/ecommerce-cielo/reference/payment-status
     */
    public function testConstantsMatchOfficialApiCodes(): void
    {
        $this->assertSame(0, TransactionStatus::NOT_FINISHED);
        $this->assertSame(1, TransactionStatus::AUTHORIZED);
        $this->assertSame(2, TransactionStatus::PAYMENT_CONFIRMED);
        $this->assertSame(3, TransactionStatus::DENIED);
        $this->assertSame(10, TransactionStatus::VOIDED);
        $this->assertSame(11, TransactionStatus::REFUNDED);
        $this->assertSame(12, TransactionStatus::PENDING);
        $this->assertSame(13, TransactionStatus::ABORTED);
        $this->assertSame(20, TransactionStatus::SCHEDULED);
    }

    public function testClassIsNotInstantiable(): void
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Call to private');

        // @phpstan-ignore-next-line
        new TransactionStatus();
    }
}
