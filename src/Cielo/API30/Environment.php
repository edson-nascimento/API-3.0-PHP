<?php

namespace Cielo\API30;

/**
 * Interface Environment.
 */
interface Environment
{
    /**
     * Gets the environment's Api URL.
     *
     * @return string the Api URL
     */
    public function getApiUrl();

    /**
     * Gets the environment's Api Query URL.
     *
     * @return string the Api Query URL
     */
    public function getApiQueryUrl();

    /**
     * Gets the environment's MPI (Braspag 3DS) URL.
     *
     * @return string the MPI URL
     */
    public function getMpiUrl();
}
