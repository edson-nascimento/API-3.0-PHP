<?php

namespace Cielo\API30\Ecommerce;

/**
 * Class Customer.
 */
class Customer implements \JsonSerializable
{
    private $name;

    private $email;

    private $birthDate;

    private $identity;

    private $identityType;

    private $address;

    private $deliveryAddress;

    /**
     * Customer constructor.
     *
     * @param null $name
     */
    public function __construct($name = null)
    {
        $this->setName($name);
    }

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }

    public function populate(\stdClass $data)
    {
        $this->name = isset($data->Name) ? $data->Name : null;
        $this->email = isset($data->Email) ? $data->Email : null;
        $this->birthDate = isset($data->Birthdate) ? $data->Birthdate : null;

        $this->identity = isset($data->Identity) ? $data->Identity : null;
        $this->identityType = isset($data->IdentityType) ? $data->IdentityType : null;

        if (isset($data->Address)) {
            $this->address = new Address();
            $this->address->populate($data->Address);
        }

        if (isset($data->DeliveryAddress)) {
            $this->deliveryAddress = new Address();
            $this->deliveryAddress->populate($data->DeliveryAddress);
        }
    }

    /**
     * @return Address
     */
    public function address()
    {
        $address = new Address();

        $this->setAddress($address);

        return $address;
    }

    /**
     * @return Address
     */
    public function deliveryAddress()
    {
        $address = new Address();

        $this->setDeliveryAddress($address);

        return $address;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @return $this
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @return $this
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;

        return $this;
    }

    public function getIdentityType()
    {
        return $this->identityType;
    }

    /**
     * @return $this
     */
    public function setIdentityType($identityType)
    {
        $this->identityType = $identityType;

        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * @return $this
     */
    public function setDeliveryAddress($deliveryAddress)
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }
}
