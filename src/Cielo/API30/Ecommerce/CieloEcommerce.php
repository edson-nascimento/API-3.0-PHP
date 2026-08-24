<?php

namespace Cielo\API30\Ecommerce;

use Cielo\API30\Ecommerce\Request\CreateSaleRequest;
use Cielo\API30\Ecommerce\Request\QueryRecurrentPaymentRequest;
use Cielo\API30\Ecommerce\Request\QuerySaleRequest;
use Cielo\API30\Ecommerce\Request\TokenizeCardRequest;
use Cielo\API30\Ecommerce\Request\UpdateSaleRequest;
use Cielo\API30\Ecommerce\Service\RequestService;
use Cielo\API30\Ecommerce\Service\ThreeDSecureService;
use Cielo\API30\Merchant;
use Psr\Log\LoggerInterface;

/**
 * The Cielo Ecommerce SDK front-end;.
 */
class CieloEcommerce
{
    private $merchant;

    private $environment;

    private $logger;

    /**
     * Create an instance of CieloEcommerce choosing the environment where the
     * requests will be send.
     */
    public function __construct(Merchant $merchant, ?Environment $environment = null, ?LoggerInterface $logger = null)
    {
        if ($environment == null) {
            $environment = Environment::production();
        }

        $this->merchant = $merchant;
        $this->environment = $environment;
        $this->logger = $logger;
    }

    /**
     * Send the Sale to be created and return the Sale with tid and the status
     * returned by Cielo.
     *
     * @param Sale $sale
     *                   The preconfigured Sale
     *
     * @return Sale The Sale with authorization, tid, etc. returned by Cielo.
     *
     * @throws \Cielo\API30\Ecommerce\Request\CieloRequestException if anything gets wrong
     *
     * @see <a href=
     *      "https://developercielo.github.io/Webservice-3.0/english.html#error-codes">Error
     *      Codes</a>
     */
    public function createSale(Sale $sale)
    {
        $createSaleRequest = new CreateSaleRequest($this->merchant, $this->environment, $this->logger);

        return $createSaleRequest->execute($sale);
    }

    /**
     * Query a Sale on Cielo by paymentId.
     *
     * @param string $paymentId
     *                          The paymentId to be queried
     *
     * @return Sale The Sale with authorization, tid, etc. returned by Cielo.
     *
     * @throws \Cielo\API30\Ecommerce\Request\CieloRequestException if anything gets wrong
     *
     * @see <a href=
     *      "https://developercielo.github.io/Webservice-3.0/english.html#error-codes">Error
     *      Codes</a>
     */
    public function getSale($paymentId)
    {
        $querySaleRequest = new QuerySaleRequest($this->merchant, $this->environment, $this->logger);

        return $querySaleRequest->execute($paymentId);
    }

    /**
     * Query a RecurrentPayment on Cielo by RecurrentPaymentId.
     *
     * @param string $recurrentPaymentId
     *                                   The RecurrentPaymentId to be queried
     *
     * @return \Cielo\API30\Ecommerce\RecurrentPayment
     *                                                 The RecurrentPayment with authorization, tid, etc. returned by Cielo.
     *
     * @throws \Cielo\API30\Ecommerce\Request\CieloRequestException if anything gets wrong
     *
     * @see <a href=
     *      "https://developercielo.github.io/Webservice-3.0/english.html#error-codes">Error
     *      Codes</a>
     */
    public function getRecurrentPayment($recurrentPaymentId)
    {
        $queryRecurrentPaymentRequest = new queryRecurrentPaymentRequest($this->merchant, $this->environment, $this->logger);

        return $queryRecurrentPaymentRequest->execute($recurrentPaymentId);
    }

    /**
     * Cancel a Sale on Cielo by paymentId and speficying the amount.
     *
     * @param string $paymentId
     *                          The paymentId to be queried
     * @param int    $amount
     *                          Order value in cents
     *
     * @return \Cielo\API30\Ecommerce\Payment
     *
     * @throws \Cielo\API30\Ecommerce\Request\CieloRequestException if anything gets wrong
     *
     * @see <a href=
     *      "https://developercielo.github.io/Webservice-3.0/english.html#error-codes">Error
     *      Codes</a>
     */
    public function cancelSale($paymentId, $amount = null)
    {
        $updateSaleRequest = new UpdateSaleRequest('void', $this->merchant, $this->environment, $this->logger);

        $updateSaleRequest->setAmount($amount);

        return $updateSaleRequest->execute($paymentId);
    }

    /**
     * Capture a Sale on Cielo by paymentId and specifying the amount and the
     * serviceTaxAmount.
     *
     * @param string $paymentId
     *                                 The paymentId to be captured
     * @param int    $amount
     *                                 Amount of the authorization to be captured
     * @param int    $serviceTaxAmount
     *                                 Amount of the authorization should be destined for the service
     *                                 charge
     *
     * @return \Cielo\API30\Ecommerce\Payment the captured Payment
     *
     * @throws \Cielo\API30\Ecommerce\Request\CieloRequestException if anything gets wrong
     *
     * @see <a href=
     *      "https://developercielo.github.io/Webservice-3.0/english.html#error-codes">Error
     *      Codes</a>
     */
    public function captureSale($paymentId, $amount = null, $serviceTaxAmount = null)
    {
        $updateSaleRequest = new UpdateSaleRequest('capture', $this->merchant, $this->environment, $this->logger);

        $updateSaleRequest->setAmount($amount);
        $updateSaleRequest->setServiceTaxAmount($serviceTaxAmount);

        return $updateSaleRequest->execute($paymentId);
    }

    /**
     * @return CreditCard
     */
    public function tokenizeCard(CreditCard $card)
    {
        $tokenizeCardRequest = new TokenizeCardRequest($this->merchant, $this->environment, $this->logger);

        return $tokenizeCardRequest->execute($card);
    }

    /**
     * Cria o token de acesso 3DS (Cielo OAuth / Braspag MPI) para uso no script de autenticação do front-end.
     *
     * @see https://docs.cielo.com.br/ecommerce-cielo/docs/token-acesso
     */
    public function create3DSAccessToken(
        string $clientId,
        string $clientSecret,
        int $establishmentCode,
        string $merchantName,
        int $mcc,
    ): AccessToken {
        $threeDSecureService = new ThreeDSecureService($this->merchant, $this->environment, $this->logger);

        $threeDSecureService->setClientId($clientId)
            ->setClientSecret($clientSecret)
            ->setEstablishmentCode($establishmentCode)
            ->setMerchantName($merchantName)
            ->setMcc($mcc);

        return $threeDSecureService->generateAccessToken();
    }

    public function requestService(): RequestService
    {
        return new RequestService($this->merchant, $this->environment, $this->logger);
    }
}
