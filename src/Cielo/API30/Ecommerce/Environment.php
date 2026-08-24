<?php

namespace Cielo\API30\Ecommerce;

/**
 * Class Environment.
 */
class Environment implements \Cielo\API30\Environment
{
    private $api;

    private $apiQuery;

    private $mpi;

    /**
     * Environment constructor.
     */
    private function __construct($api, $apiQuery, $mpi)
    {
        $this->api = $api;
        $this->apiQuery = $apiQuery;
        $this->mpi = $mpi;
    }

    /**
     * @return Environment
     */
    public static function sandbox()
    {
        return new Environment(
            api: 'https://apisandbox.cieloecommerce.cielo.com.br/',
            apiQuery: 'https://apiquerysandbox.cieloecommerce.cielo.com.br/',
            mpi: 'https://mpisandbox.braspag.com.br/',
        );
    }

    /**
     * @return Environment
     */
    public static function production()
    {
        return new Environment(
            api: 'https://api.cieloecommerce.cielo.com.br/',
            apiQuery: 'https://apiquery.cieloecommerce.cielo.com.br/',
            mpi: 'https://mpi.braspag.com.br/',
        );
    }

    /**
     * Gets the environment's Api URL.
     *
     * @return string the Api URL
     */
    public function getApiUrl()
    {
        return $this->api;
    }

    /**
     * Gets the environment's Api Query URL.
     *
     * @return string Api Query URL
     */
    public function getApiQueryUrl()
    {
        return $this->apiQuery;
    }

    /**
     * Gets the environment's MPI (Braspag 3DS) URL.
     *
     * @return string the MPI URL
     */
    public function getMpiUrl()
    {
        return $this->mpi;
    }
}
