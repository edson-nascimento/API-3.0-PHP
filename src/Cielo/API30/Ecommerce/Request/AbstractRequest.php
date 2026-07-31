<?php

namespace Cielo\API30\Ecommerce\Request;

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
        $headers = [
            'Accept: application/json',
            // 'Accept-Encoding: gzip',
            'User-Agent: CieloEcommerce/3.0 PHP SDK',
            'MerchantId: ' . $this->merchant->getId(),
            'MerchantKey: ' . $this->merchant->getKey(),
            'RequestId: ' . uniqid(),
        ];

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

        switch ($method) {
            case 'GET':
                break;
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, true);
                break;
            default:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }

        if ($content !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($content));

            $headers[] = 'Content-Type: application/json';
        } else {
            $headers[] = 'Content-Length: 0';
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if ($this->logger !== null) {
            $this->logger->debug('Requisição', [
                sprintf('%s %s', $method, $url),
                $headers,
                json_decode(preg_replace('/("cardnumber"):"([^"]{6})[^"]+([^"]{4})"/i', '$1:"$2******$3"', json_encode($content))),
            ]
            );
        }

        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($this->logger !== null) {
            $this->logger->debug('Resposta', [
                sprintf('Código de status: %s', $statusCode),
                json_decode($response),
            ]);
        }

        if (curl_errno($curl)) {
            $message = sprintf('cURL error[%s]: %s', curl_errno($curl), curl_error($curl));

            if ($this->logger !== null) {
                $this->logger->error($message);
            }

            throw new \RuntimeException($message);
        }

        curl_close($curl);

        return $this->readResponse($statusCode, $response);
    }

    /**
     * @throws CieloRequestException
     */
    protected function readResponse($statusCode, $responseBody)
    {
        $unserialized = null;

        switch ($statusCode) {
            case 200:
            case 201:
                $unserialized = $this->unserialize($responseBody);
                break;
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

        return $unserialized;
    }

    abstract protected function unserialize($json);
}
