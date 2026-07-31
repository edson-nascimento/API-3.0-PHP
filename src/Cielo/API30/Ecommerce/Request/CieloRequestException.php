<?php

namespace Cielo\API30\Ecommerce\Request;

/**
 * Class CieloRequestException.
 */
class CieloRequestException extends \Exception
{
    private $cieloError;

    /**
     * CieloRequestException constructor.
     *
     * @param string $message
     * @param int    $code
     * @param null   $previous
     */
    public function __construct($message, $code, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getCieloError()
    {
        return $this->cieloError;
    }

    /**
     * @return $this
     */
    public function setCieloError(CieloError $cieloError)
    {
        $this->cieloError = $cieloError;

        return $this;
    }
}
