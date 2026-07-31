<?php

namespace Cielo\API30\Ecommerce\Request;

use Cielo\API30\Ecommerce\CreditCard;
use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Merchant;
use Psr\Log\LoggerInterface;

/**
 * Class CreateCardTokenRequestHandler.
 */
class TokenizeCardRequest extends AbstractRequest
{
    private $environment;

    /**
     * TODO not used.
     *
     * @var Merchant
     *
     * @phpstan-ignore-next-line
     */
    private $merchant;

    /**
     * CreateCardTokenRequestHandler constructor.
     */
    public function __construct(Merchant $merchant, Environment $environment, ?LoggerInterface $logger = null)
    {
        parent::__construct($merchant, $logger);

        $this->merchant = $merchant;
        $this->environment = $environment;
    }

    public function execute($param)
    {
        $url = $this->environment->getApiUrl() . '1/card/';

        return $this->sendRequest('POST', $url, $param);
    }

    protected function unserialize($json)
    {
        return CreditCard::fromJson($json);
    }
}
