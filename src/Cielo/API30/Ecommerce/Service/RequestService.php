<?php

namespace Cielo\API30\Ecommerce\Service;

use Cielo\API30\Http\CieloHttpResponse;

class RequestService extends AbstractService
{
    /**
     * @param string|array<string|int,mixed>|object|null $body
     * @param array<string|int, string>                  $headers
     */
    public function apiRequest(string $method, string $endpoint, string|array|object|null $body = null, array $headers = []): CieloHttpResponse
    {
        return $this->getHttpClient()->request(
            method: $method,
            url: $this->buildUrl($this->environment->getApiUrl(), $endpoint),
            body: $body,
            headers: \array_merge($this->getApiAuthHeaders(), $headers),
        );
    }

    /** @param array<string|int, string>        $headers */
    public function apiQueryRequest(string $endpoint, array $headers = []): CieloHttpResponse
    {
        return $this->getHttpClient()->request(
            method: 'GET',
            url: $this->buildUrl($this->environment->getApiQueryUrl(), $endpoint),
            headers: \array_merge($this->getApiAuthHeaders(), $headers),
        );
    }

    /**
     * @param string|array<string|int,mixed>|object|null $body
     * @param array<string|int, string>                  $headers
     */
    public function mpiApiRequest(string $method, string $endpoint, string|array|object|null $body = null, array $headers = []): CieloHttpResponse
    {
        return $this->getHttpClient()->request(
            method: $method,
            url: $this->buildUrl($this->environment->getMpiUrl(), $endpoint),
            body: $body,
            headers: \array_merge($this->getApiAuthHeaders(), $headers),
        );
    }

    private function buildUrl(string $baseUrl, string $endpoint): string
    {
        return $baseUrl . '/' . ltrim($endpoint, '/');
    }
}
