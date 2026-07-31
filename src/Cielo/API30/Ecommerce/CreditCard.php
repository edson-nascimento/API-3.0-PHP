<?php

namespace Cielo\API30\Ecommerce;

/**
 * Class CreditCard.
 */
class CreditCard implements \JsonSerializable, CieloSerializable
{
    /**
     * Bandeira Visa.
     */
    public const VISA = 'Visa';

    /**
     * Bandeira Mastercard.
     */
    public const MASTERCARD = 'Master';

    /**
     * Bandeira American Express.
     */
    public const AMEX = 'Amex';

    /**
     * Bandeira ELO.
     */
    public const ELO = 'Elo';

    /**
     * Bandeira Aura.
     */
    public const AURA = 'Aura';

    /**
     * Bandeira JCB.
     */
    public const JCB = 'JCB';

    /**
     * Bandeira Diners.
     */
    public const DINERS = 'Diners';

    /**
     * Bandeira Discover.
     */
    public const DISCOVER = 'Discover';

    /**
     * Bandeira Hipercard.
     */
    public const HIPERCARD = 'Hipercard';

    /** @var string */
    private $cardNumber;

    /** @var string */
    private $holder;

    /** @var string */
    private $expirationDate;

    /** @var string */
    private $securityCode;

    /** @var bool */
    private $saveCard = false;

    /** @var string */
    private $brand;

    /** @var string */
    private $cardToken;

    /** @var string */
    private $customerName;

    /** @var \stdClass */
    private $links;

    /**
     * @param string $json
     *
     * @return CreditCard
     */
    public static function fromJson($json)
    {
        $object = \json_decode($json);
        $cardToken = new CreditCard();
        $cardToken->populate($object);

        return $cardToken;
    }

    public function populate(\stdClass $data)
    {
        $this->cardNumber = isset($data->CardNumber) ? $data->CardNumber : null;
        $this->holder = isset($data->Holder) ? $data->Holder : null;
        $this->expirationDate = isset($data->ExpirationDate) ? $data->ExpirationDate : null;
        $this->securityCode = isset($data->SecurityCode) ? $data->SecurityCode : null;
        $this->saveCard = isset($data->SaveCard) ? (bool) $data->SaveCard : false;
        $this->brand = isset($data->Brand) ? $data->Brand : null;
        $this->cardToken = isset($data->CardToken) ? $data->CardToken : null;
        $this->links = isset($data->Links) ? $data->Links : new \stdClass();
        $this->customerName = isset($data->CustomerName) ? $data->CustomerName : null;
    }

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }

    public function getCardNumber()
    {
        return $this->cardNumber;
    }

    /**
     * @return $this
     */
    public function setCardNumber($cardNumber)
    {
        $this->cardNumber = $cardNumber;

        return $this;
    }

    public function getHolder()
    {
        return $this->holder;
    }

    /**
     * @return $this
     */
    public function setHolder($holder)
    {
        $this->holder = $holder;

        return $this;
    }

    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * @return $this
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    public function getSecurityCode()
    {
        return $this->securityCode;
    }

    /**
     * @return $this
     */
    public function setSecurityCode($securityCode)
    {
        $this->securityCode = $securityCode;

        return $this;
    }

    /**
     * @return bool
     */
    public function getSaveCard()
    {
        return $this->saveCard;
    }

    /**
     * @return $this
     */
    public function setSaveCard($saveCard)
    {
        $this->saveCard = $saveCard;

        return $this;
    }

    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @return $this
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    public function getCardToken()
    {
        return $this->cardToken;
    }

    /**
     * @return $this
     */
    public function setCardToken($cardToken)
    {
        $this->cardToken = $cardToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerName()
    {
        return $this->customerName;
    }

    /**
     * @param string $customerName
     */
    public function setCustomerName($customerName)
    {
        $this->customerName = $customerName;
    }

    /**
     * @return \stdClass
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param \stdClass $links
     */
    public function setLinks($links)
    {
        $this->links = $links;
    }
}
