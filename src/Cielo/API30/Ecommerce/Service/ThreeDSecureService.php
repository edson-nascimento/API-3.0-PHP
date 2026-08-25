<?php

namespace Cielo\API30\Ecommerce\Service;

use Cielo\API30\Ecommerce\AccessToken;
use Cielo\API30\Ecommerce\Request\CieloRequestException;
use Cielo\API30\Http\CieloHttpClient;
use Cielo\API30\Http\CieloHttpResponse;

/**
 * Cria o token de acesso 3DS (Cielo OAuth / Braspag MPI) que deve ser
 * repassado ao script de autenticação no front-end.
 *
 * Autentica via Basic Authentication com base64(ClientId:ClientSecret) e não
 * utiliza as credenciais de lojista (MerchantId/MerchantKey), por isso não
 * estende AbstractRequest.
 *
 * @see https://docs.cielo.com.br/ecommerce-cielo/docs/token-acesso
 */
class ThreeDSecureService extends AbstractService
{
    private ?string $clientId = null;

    private ?string $clientSecret = null;

    private ?int $establishmentCode = null;

    private ?string $merchantName = null;

    private ?int $mcc = null;

    /**
     * @throws CieloRequestException
     * @throws \RuntimeException
     */
    public function generateAccessToken(): AccessToken
    {
        $url = $this->environment->getMpiUrl() . 'v2/auth/token';

        $authorization = 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret);

        $payload = [
            'EstablishmentCode' => $this->establishmentCode,
            'MerchantName' => $this->merchantName,
            'MCC' => $this->mcc,
        ];

        $response = $this->getHttpClient()->request(
            CieloHttpClient::POST,
            $url,
            $payload,
            ['Authorization' => $authorization]
        );

        return $this->populateAccessToken($response);
    }

    /**
     * @see https://docs.cielo.com.br/ecommerce-cielo/docs/mpi-v3#etapa-3--init-inicializar-a-sess%C3%A3o
     */
    public function generateAccessTokenMpiV3(): AccessToken
    {
        $url = $this->environment->getMpiUrl() . 'v3/auth/token';

        $authorization = 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret);

        $payload = [
            'EstablishmentCode' => $this->establishmentCode,
            'MerchantName' => $this->merchantName,
            'MCC' => $this->mcc,
        ];

        $response = $this->getHttpClient()->request(
            CieloHttpClient::POST,
            $url,
            $payload,
            [
                'Authorization' => $authorization,
            ]
        );

        return $this->populateAccessToken($response);
    }

    /** @throws CieloRequestException */
    private function populateAccessToken(CieloHttpResponse $response): AccessToken
    {
        switch ($response->getStatusCode()) {
            case 200:
            case 201:
                return AccessToken::fromJson($response->getBody());
            case 400:
                throw new CieloRequestException("Request Error {$response->getStatusCode()}, response: {$response->getBody()}", $response->getStatusCode());
            case 401:
                throw new CieloRequestException('HTTP 401 NotAuthorized', $response->getStatusCode(), null);
            case 404:
                throw new CieloRequestException('Resource not found', 404, null);
            default:
                throw new CieloRequestException("Unknown statusCode {$response->getStatusCode()}, response: {$response->getBody()}", $response->getStatusCode());
        }
    }

    // gets and sets
    public function setClientId(?string $clientId): static
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function setClientSecret(?string $clientSecret): static
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    public function setEstablishmentCode(?int $establishmentCode): static
    {
        $this->establishmentCode = $establishmentCode;

        return $this;
    }

    public function setMerchantName(?string $merchantName): static
    {
        $this->merchantName = $merchantName;

        return $this;
    }

    public function setMcc(?int $mcc): static
    {
        $this->mcc = $mcc;

        return $this;
    }
}
