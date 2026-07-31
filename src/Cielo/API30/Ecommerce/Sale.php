<?php

namespace Cielo\API30\Ecommerce;

/**
 * Class Sale.
 */
class Sale implements \JsonSerializable
{
    private $merchantOrderId;

    private $customer;

    private $payment;

    /**
     * Sale constructor.
     *
     * @param string|int $merchantOrderId
     */
    public function __construct($merchantOrderId = null)
    {
        $this->setMerchantOrderId($merchantOrderId);
    }

    /**
     * @return Sale
     */
    public static function fromJson($json)
    {
        $object = json_decode($json) ?: json_decode(gzdecode($json));

        $sale = new Sale();
        $sale->populate($object);

        return $sale;
    }

    public function populate(\stdClass $data)
    {
        $dataProps = get_object_vars($data);

        if (isset($dataProps['Customer'])) {
            $this->customer = new Customer();
            $this->customer->populate($data->Customer);
        }

        if (isset($dataProps['Payment'])) {
            $this->payment = new Payment();
            $this->payment->populate($data->Payment);
        }

        if (isset($dataProps['MerchantOrderId'])) {
            $this->merchantOrderId = $data->MerchantOrderId;
        }
    }

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }

    /**
     * @return Customer
     */
    public function customer($name)
    {
        $customer = new Customer($name);

        $this->setCustomer($customer);

        return $customer;
    }

    /**
     * @param int $installments
     *
     * @return Payment
     */
    public function payment($amount, $installments = 1)
    {
        $payment = new Payment($amount, $installments);

        $this->setPayment($payment);

        return $payment;
    }

    public function getMerchantOrderId()
    {
        return $this->merchantOrderId;
    }

    /**
     * @return $this
     */
    public function setMerchantOrderId($merchantOrderId)
    {
        $this->merchantOrderId = $merchantOrderId;

        return $this;
    }

    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return $this
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    public function getPayment()
    {
        return $this->payment;
    }

    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;

        return $this;
    }
}
