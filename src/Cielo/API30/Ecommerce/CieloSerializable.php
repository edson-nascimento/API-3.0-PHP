<?php

namespace Cielo\API30\Ecommerce;

/**
 * Interface CieloSerializable.
 */
interface CieloSerializable extends \JsonSerializable
{
    /**
     * @return void
     */
    public function populate(\stdClass $data);
}
