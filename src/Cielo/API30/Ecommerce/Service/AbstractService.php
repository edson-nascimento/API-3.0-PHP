<?php

namespace Cielo\API30\Ecommerce\Service;

use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Http\CieloHttpClient;
use Cielo\API30\Merchant;
use Psr\Log\LoggerInterface;

abstract class AbstractService
{
    private CieloHttpClient $httpClient;

    public function __construct(
        protected Merchant $merchant,
        protected Environment $environment,
        protected ?LoggerInterface $logger = null,
        ?CieloHttpClient $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?? new CieloHttpClient($this->logger);
    }

    protected function getHttpClient(): CieloHttpClient
    {
        return $this->httpClient;
    }

    /** @return array{MerchantId: string, MerchantKey: string, RequestId: string} */
    protected function getApiAuthHeaders(): array
    {
        return [
            // 'Accept-Encoding' => 'gzip',
            'MerchantId' => $this->merchant->getId(),
            'MerchantKey' => $this->merchant->getKey(),
            'RequestId' => uniqid(),
        ];
    }
}
