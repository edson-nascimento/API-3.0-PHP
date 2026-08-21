<?php

namespace Cielo\API30\Ecommerce\Service;

use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Http\CieloHttpClient;
use Cielo\API30\Http\CieloHttpResponse;
use Psr\Log\LoggerInterface;

abstract class AbstractService
{
    private CieloHttpClient $httpClient;

    public function __construct(
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

    /**
     * @param array<string, mixed>|object $body
     * @param array<string, string>       $headers
     */
    public function sendRequest(string $method, string $endpoint, array|object $body = [], array $headers = []): CieloHttpResponse
    {
        return $this->getHttpClient()->request($method, $endpoint, $body, $headers);
    }
}
