<?php

namespace Cielo\API30\Ecommerce\Request;

use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Environment;
use Cielo\API30\Merchant;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateSaleRequest.
 */
class UpdateSaleRequest extends AbstractRequest
{
    private $environment;

    private $type;

    private $serviceTaxAmount;

    private $amount;

    /**
     * UpdateSaleRequest constructor.
     *
     * @param string $type
     */
    public function __construct($type, Merchant $merchant, Environment $environment, ?LoggerInterface $logger = null)
    {
        parent::__construct($merchant, $logger);

        $this->environment = $environment;
        $this->type = $type;
    }

    /**
     * @return Payment
     *
     * @throws \Cielo\API30\Ecommerce\Request\CieloRequestException
     * @throws \RuntimeException
     */
    public function execute($paymentId)
    {
        $url = $this->environment->getApiUrl() . '1/sales/' . $paymentId . '/' . $this->type;
        $params = [];

        if ($this->amount != null) {
            $params['amount'] = $this->amount;
        }

        if ($this->serviceTaxAmount != null) {
            $params['serviceTaxAmount'] = $this->serviceTaxAmount;
        }

        $url .= '?' . http_build_query($params);

        return $this->sendRequest('PUT', $url);
    }

    /**
     * @return Payment
     */
    protected function unserialize($json)
    {
        return Payment::fromJson($json);
    }

    public function getServiceTaxAmount()
    {
        return $this->serviceTaxAmount;
    }

    /**
     * @return $this
     */
    public function setServiceTaxAmount($serviceTaxAmount)
    {
        $this->serviceTaxAmount = $serviceTaxAmount;

        return $this;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }
}
