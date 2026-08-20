<?php

namespace Cielo\API30\Http;

use Psr\Log\LoggerInterface;

/**
 * @phpstan-type HttpMethod self::GET|self::POST|self::PUT|self::DELETE
 */
class CieloHttpClient
{
    public const GET = 'GET';

    public const POST = 'POST';

    public const PUT = 'PUT';

    public const DELETE = 'DELETE';

    public const CONTENT_TYPE_JSON = 'application/json';

    public const CONTENT_TYPE_FORM_URLENCODED = 'application/x-www-form-urlencoded';

    private ?LoggerInterface $logger;

    private string $userAgent = 'CieloEcommerce/3.0 PHP SDK';

    /**
     * CieloHttpClient constructor.
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function setUserAgent(string $userAgent): self
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * @param HttpMethod                                 $method
     * @param string|array<string|int,mixed>|object|null $body
     * @param array<string|int, string>                  $headers headers no formato ['Nome' => 'Valor'] ou linhas 'Nome: Valor'
     *
     * @throws \RuntimeException em caso de erro de transporte (cURL)
     */
    public function request(
        string $method,
        string $url,
        string|array|object|null $body = null,
        array $headers = [],
        string $contentType = self::CONTENT_TYPE_JSON,
    ): CieloHttpResponse {
        $curl = curl_init($url);

        if (!$curl instanceof \CurlHandle) {
            throw new \RuntimeException('Não foi possível inicializar o cURL.');
        }

        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        switch ($method) {
            case self::GET:
                break;
            case self::POST:
                curl_setopt($curl, CURLOPT_POST, true);
                break;
            default:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }

        $requestHeaders = array_merge([
            'Accept' => 'application/json',
            'User-Agent' => $this->userAgent,
        ], $headers);

        $parsedBody = $this->parseBody($body, $contentType);

        if ($parsedBody !== '') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $parsedBody);
            $requestHeaders['Content-Type'] = $contentType;
        } else {
            $requestHeaders['Content-Length'] = '0';
        }

        $formattedHeaders = $this->formatHeaders($requestHeaders);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $formattedHeaders);

        if ($this->logger !== null) {
            $this->logger->debug('Request', [
                sprintf('%s %s', $method, $url),
                $this->maskHeaders($formattedHeaders),
                $this->maskBody($parsedBody),
            ]);
        }

        $response = curl_exec($curl);
        $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $body = is_string($response) ? $response : '';

        if ($this->logger !== null) {
            $this->logger->debug('Response', [
                sprintf('Status code: %s', $statusCode),
                $this->maskBody($body),
            ]);
        }

        if (curl_errno($curl)) {
            $message = sprintf('cURL error[%s]: %s', curl_errno($curl), curl_error($curl));

            if ($this->logger !== null) {
                $this->logger->error($message);
            }

            throw new \RuntimeException($message);
        }

        return new CieloHttpResponse($statusCode, $body);
    }

    private function parseBody(mixed $body, string $contentType): string
    {
        if (empty($body)) {
            return '';
        }

        if (is_string($body)) {
            return $body;
        }

        if ($contentType === self::CONTENT_TYPE_FORM_URLENCODED) {
            return http_build_query((array) $body);
        }

        return json_encode($body) ?: '';
    }

    /**
     * Normaliza os headers para o formato esperado pelo cURL (linhas "Nome: Valor").
     *
     * Aceita tanto o formato associativo (['Nome' => 'Valor']) quanto linhas já
     * formatadas passadas com chave numérica (['Nome: Valor']).
     *
     * @param array<string|int, string> $headers
     *
     * @return string[]
     */
    protected function formatHeaders(array $headers): array
    {
        $formatted = [];

        foreach ($headers as $name => $value) {
            $header = is_numeric($name) ? trim($value) : trim($name) . ': ' . trim($value);

            if ($header) {
                $formatted[] = $header;
            }
        }

        return $formatted;
    }

    /**
     * Mascara o valor de headers sensíveis (ex.: Authorization) nos logs.
     *
     * @param string[] $headers
     *
     * @return string[]
     */
    protected function maskHeaders(array $headers): array
    {
        return array_map(static function ($header) {
            if (stripos($header, 'Authorization:') === 0) {
                $value = trim(substr($header, strlen('Authorization:')));
                $scheme = strtok($value, ' ');

                return 'Authorization: ' . ($scheme !== false ? $scheme . ' ' : '') . '******';
            }

            return $header;
        }, $headers);
    }

    /**
     * Mascara dados sensíveis (número de cartão, CVV, tokens) nos logs.
     */
    protected function maskBody(string $body): string
    {
        if ($body === '') {
            return '';
        }

        $masked = preg_replace(
            '/("cardNumber"\s*:\s*")([^"]{6})[^"]+([^"]{4})"/i',
            '$1$2******$3"',
            $body
        ) ?? $body;

        $masked = preg_replace('/"(securityCode|holder|cardToken|accessToken|access_token)":"[^"]+"/i', '"\1":"***"', $masked);

        return $masked;
    }
}
