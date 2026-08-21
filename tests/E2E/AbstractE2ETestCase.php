<?php

namespace TestApp\E2E;

use Cielo\API30\Ecommerce\CieloEcommerce;
use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Merchant;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

abstract class AbstractE2ETestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getCieloEcommerce(): CieloEcommerce
    {
        $merchantId = (string) getenv('CIELO_MERCHANT_ID');
        $merchantKey = (string) getenv('CIELO_MERCHANT_KEY');

        if (empty($merchantId) || empty($merchantKey)) {
            throw new \RuntimeException('Você precisa informar seu Merchant ID e Merchant Key para rodar os testes');
        }

        $debug = (int) getenv('CIELO_DEBUG');
        $logger = null;
        if ($debug) {
            $logger = new Logger('Cielo Ecommerce Test');
            $logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));
        }

        $merchant = new Merchant($merchantId, $merchantKey);

        return new CieloEcommerce($merchant, Environment::sandbox(), $logger);
    }

    protected function uniqueMerchantOrderId(string $prefix = 'E2E'): string
    {
        $orderId = preg_replace('/[^A-Za-z0-9]/', '', $prefix . bin2hex(random_bytes(8)));

        return substr((string) $orderId, 0, 50);
    }
}
