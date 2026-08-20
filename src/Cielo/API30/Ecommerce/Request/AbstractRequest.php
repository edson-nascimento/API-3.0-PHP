<?php

namespace Cielo\API30\Ecommerce\Request;

use Cielo\API30\Http\CieloHttpClient;
use Cielo\API30\Merchant;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractSaleRequest.
 */
abstract class AbstractRequest
{
    private $merchant;
    private $logger;

    /**
     * AbstractSaleRequest constructor.
     */
    public function __construct(Merchant $merchant, ?LoggerInterface $logger = null)
    {
        $this->merchant = $merchant;
        $this->logger = $logger;
    }

    abstract public function execute($param);

    /**
     * @throws \Cielo\API30\Ecommerce\Request\CieloRequestException
     * @throws \RuntimeException
     */
    protected function sendRequest($method, $url, ?\JsonSerializable $content = null)
    {
        $client = new CieloHttpClient($this->logger);

        $headers = [
            // 'Accept-Encoding' => 'gzip',
            'MerchantId' => $this->merchant->getId(),
            'MerchantKey' => $this->merchant->getKey(),
            'RequestId' => uniqid(),
        ];

        $response = $client->request($method, $url, $content, $headers);

        return $this->readResponse($response->getStatusCode(), $response->getBody());
    }

    /**
     * @throws CieloRequestException
     */
    protected function readResponse($statusCode, $responseBody)
    {
        switch ($statusCode) {
            case 200:
            case 201:
                return $this->unserialize($responseBody);
            case 400:
                $exception = null;
                $response = json_decode($responseBody);

                if (is_array($response) && count($response) > 0) {
                    foreach ($response as $error) {
                        $cieloError = new CieloError($error->Message, $error->Code);
                        $exception = new CieloRequestException('Request Error', $statusCode, $exception);
                        $exception->setCieloError($cieloError);
                    }
                } else {
                    $exception = new CieloRequestException("Request Error $statusCode, response: $responseBody", $statusCode);
                }

                throw $exception;
            case 401:
                throw new CieloRequestException('HTTP 401 NotAuthorized', $statusCode, null);
            case 404:
                throw new CieloRequestException('Resource not found', 404, null);
            default:
                throw new CieloRequestException("Unknown statusCode $statusCode, response: $responseBody", $statusCode);
        }
    }

    abstract protected function unserialize($json);
}
