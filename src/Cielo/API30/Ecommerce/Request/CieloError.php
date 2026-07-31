<?php

namespace Cielo\API30\Ecommerce\Request;

/**
 * Class CieloError.
 */
class CieloError
{
    private $code;

    private $message;

    /**
     * CieloError constructor.
     */
    public function __construct($message, $code)
    {
        $this->setMessage($message);
        $this->setCode($code);
    }

    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }
}
