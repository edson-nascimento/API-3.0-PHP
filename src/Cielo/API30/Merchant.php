<?php

namespace Cielo\API30;

/**
 * Class Merchant.
 */
class Merchant
{
    private $id;
    private $key;

    /**
     * Merchant constructor.
     */
    public function __construct($id, $key)
    {
        $this->id = $id;
        $this->key = $key;
    }

    /**
     * Gets the merchant identification number.
     *
     * @return string the merchant identification number on Cielo
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the merchant identification key.
     *
     * @return string the merchant identification key on Cielo
     */
    public function getKey()
    {
        return $this->key;
    }
}
