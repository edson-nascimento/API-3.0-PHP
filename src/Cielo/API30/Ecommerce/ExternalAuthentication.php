<?php

namespace Cielo\API30\Ecommerce;

/**
 * Class ExternalAuthentication.
 *
 * Representa o nó Payment.ExternalAuthentication utilizado no fluxo de
 * autenticação 3DS 2.2 (autorização com autenticação e Data Only).
 */
class ExternalAuthentication implements \JsonSerializable, CieloSerializable
{
    /** @var string */
    private $cavv;

    /** @var string */
    private $xid;

    /** @var string */
    private $eci;

    /** @var string */
    private $version;

    /** @var string|null */
    private $referenceId;

    /** @var bool|null */
    private $dataOnly;

    /**
     * @param string $json
     *
     * @return ExternalAuthentication
     */
    public static function fromJson($json)
    {
        $object = \json_decode($json);
        $externalAuthentication = new ExternalAuthentication();
        $externalAuthentication->populate($object);

        return $externalAuthentication;
    }

    public function populate(\stdClass $data)
    {
        $this->cavv = isset($data->Cavv) ? $data->Cavv : null;
        $this->xid = isset($data->Xid) ? $data->Xid : null;
        $this->eci = isset($data->Eci) ? $data->Eci : null;
        $this->version = isset($data->Version) ? $data->Version : null;

        if (isset($data->ReferenceId)) {
            $this->referenceId = $data->ReferenceId;
        } elseif (isset($data->ReferenceID)) {
            $this->referenceId = $data->ReferenceID;
        } else {
            $this->referenceId = null;
        }

        $this->dataOnly = isset($data->DataOnly) ? (bool) $data->DataOnly : null;
    }

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }

    public function getCavv()
    {
        return $this->cavv;
    }

    /**
     * @return $this
     */
    public function setCavv($cavv)
    {
        $this->cavv = $cavv;

        return $this;
    }

    public function getXid()
    {
        return $this->xid;
    }

    /**
     * @return $this
     */
    public function setXid($xid)
    {
        $this->xid = $xid;

        return $this;
    }

    public function getEci()
    {
        return $this->eci;
    }

    /**
     * @return $this
     */
    public function setEci($eci)
    {
        $this->eci = $eci;

        return $this;
    }

    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    public function getReferenceId()
    {
        return $this->referenceId;
    }

    /**
     * @return $this
     */
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getDataOnly()
    {
        return $this->dataOnly;
    }

    /**
     * @return $this
     */
    public function setDataOnly($dataOnly)
    {
        $this->dataOnly = $dataOnly;

        return $this;
    }
}
