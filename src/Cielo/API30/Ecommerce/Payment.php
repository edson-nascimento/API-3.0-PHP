<?php

namespace Cielo\API30\Ecommerce;

/**
 * Class Payment.
 */
class Payment implements \JsonSerializable
{
    public const PAYMENTTYPE_CREDITCARD = 'CreditCard';

    public const PAYMENTTYPE_DEBITCARD = 'DebitCard';

    public const PAYMENTTYPE_ELECTRONIC_TRANSFER = 'ElectronicTransfer';

    public const PAYMENTTYPE_BOLETO = 'Boleto';

    public const PAYMENTTYPE_PIX = 'Pix';

    public const PROVIDER_BRADESCO = 'Bradesco';

    public const PROVIDER_BANCO_DO_BRASIL = 'BancoDoBrasil';

    public const PROVIDER_SIMULADO = 'Simulado';

    private $serviceTaxAmount;

    private $installments;

    private $interest;

    private $capture = false;

    private $authenticate = false;

    private $recurrent;

    private $recurrentPayment;

    private $creditCard;

    private $debitCard;

    private $authenticationUrl;

    private $tid;

    private $proofOfSale;

    private $authorizationCode;

    private $softDescriptor = '';

    private $returnUrl;

    private $provider;

    private $paymentId;

    private $type;

    private $amount;

    private $receivedDate;

    private $capturedAmount;

    private $capturedDate;

    private $voidedAmount;

    private $voidedDate;

    private $currency;

    private $country;

    private $returnCode;

    private $returnMessage;

    private $status;

    private $links;

    private $extraDataCollection;

    private $expirationDate;

    private $url;

    private $number;

    private $boletoNumber;

    private $barCodeNumber;

    private $digitableLine;

    private $address;

    private $assignor;

    private $demonstrative;

    private $identification;

    private $instructions;

    private $acquirerTransactionId;

    private $qrcodeBase64Image;

    private $qrCodeString;

    /**
     * Payment constructor.
     *
     * @param int $amount
     * @param int $installments
     */
    public function __construct($amount = 0, $installments = 1)
    {
        $this->setAmount($amount);
        $this->setInstallments($installments);
    }

    /**
     * @return Payment
     */
    public static function fromJson($json)
    {
        $payment = new Payment();
        $payment->populate(json_decode($json));

        return $payment;
    }

    public function populate(\stdClass $data)
    {
        $this->serviceTaxAmount = isset($data->ServiceTaxAmount) ? $data->ServiceTaxAmount : null;
        $this->installments = isset($data->Installments) ? $data->Installments : null;
        $this->interest = isset($data->Interest) ? $data->Interest : null;
        $this->capture = isset($data->Capture) ? (bool) $data->Capture : false;
        $this->authenticate = isset($data->Authenticate) ? (bool) $data->Authenticate : false;
        $this->recurrent = isset($data->Recurrent) ? (bool) $data->Recurrent : false;

        if (isset($data->RecurrentPayment)) {
            $this->recurrentPayment = new RecurrentPayment(false);
            $this->recurrentPayment->populate($data->RecurrentPayment);
        }

        if (isset($data->CreditCard)) {
            $this->creditCard = new CreditCard();
            $this->creditCard->populate($data->CreditCard);
        }

        if (isset($data->DebitCard)) {
            $this->debitCard = new CreditCard();
            $this->debitCard->populate($data->DebitCard);
        }

        $this->expirationDate = isset($data->ExpirationDate) ? $data->ExpirationDate : null;
        $this->url = isset($data->Url) ? $data->Url : null;
        $this->boletoNumber = isset($data->BoletoNumber) ? $data->BoletoNumber : null;
        $this->barCodeNumber = isset($data->BarCodeNumber) ? $data->BarCodeNumber : null;
        $this->digitableLine = isset($data->DigitableLine) ? $data->DigitableLine : null;
        $this->address = isset($data->Address) ? $data->Address : null;

        $this->authenticationUrl = isset($data->AuthenticationUrl) ? $data->AuthenticationUrl : null;
        $this->tid = isset($data->Tid) ? $data->Tid : null;
        $this->proofOfSale = isset($data->ProofOfSale) ? $data->ProofOfSale : null;
        $this->authorizationCode = isset($data->AuthorizationCode) ? $data->AuthorizationCode : null;
        $this->softDescriptor = isset($data->SoftDescriptor) ? $data->SoftDescriptor : null;
        $this->provider = isset($data->Provider) ? $data->Provider : null;
        $this->paymentId = isset($data->PaymentId) ? $data->PaymentId : null;
        $this->type = isset($data->Type) ? $data->Type : null;
        $this->amount = isset($data->Amount) ? $data->Amount : null;
        $this->receivedDate = isset($data->ReceivedDate) ? $data->ReceivedDate : null;
        $this->capturedAmount = isset($data->CapturedAmount) ? $data->CapturedAmount : null;
        $this->capturedDate = isset($data->CapturedDate) ? $data->CapturedDate : null;
        $this->voidedAmount = isset($data->VoidedAmount) ? $data->VoidedAmount : null;
        $this->voidedDate = isset($data->VoidedDate) ? $data->VoidedDate : null;
        $this->currency = isset($data->Currency) ? $data->Currency : null;
        $this->country = isset($data->Country) ? $data->Country : null;
        $this->returnCode = isset($data->ReturnCode) ? $data->ReturnCode : null;
        $this->returnMessage = isset($data->ReturnMessage) ? $data->ReturnMessage : null;
        $this->status = isset($data->Status) ? $data->Status : null;

        $this->links = isset($data->Links) ? $data->Links : [];

        $this->assignor = isset($data->Assignor) ? $data->Assignor : null;
        $this->demonstrative = isset($data->Demonstrative) ? $data->Demonstrative : null;
        $this->identification = isset($data->Identification) ? $data->Identification : null;
        $this->instructions = isset($data->Instructions) ? $data->Instructions : null;

        $this->acquirerTransactionId = isset($data->AcquirerTransactionId) ? $data->AcquirerTransactionId : null;
        $this->qrcodeBase64Image = isset($data->QrcodeBase64Image) ? $data->QrcodeBase64Image : null;
        $this->qrCodeString = isset($data->QrCodeString) ? $data->QrCodeString : null;
    }

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }

    /**
     * @return CreditCard
     */
    public function creditCard($securityCode, $brand)
    {
        $card = $this->newCard($securityCode, $brand);

        $this->setType(self::PAYMENTTYPE_CREDITCARD);
        $this->setCreditCard($card);

        return $card;
    }

    /**
     * @return CreditCard
     */
    private function newCard($securityCode, $brand)
    {
        $card = new CreditCard();
        $card->setSecurityCode($securityCode);
        $card->setBrand($brand);

        return $card;
    }

    /**
     * @return CreditCard
     */
    public function debitCard($securityCode, $brand)
    {
        $card = $this->newCard($securityCode, $brand);

        $this->setType(self::PAYMENTTYPE_DEBITCARD);
        $this->setDebitCard($card);

        return $card;
    }

    /**
     * @param bool $authorizeNow
     *
     * @return RecurrentPayment
     */
    public function recurrentPayment($authorizeNow = true)
    {
        $recurrentPayment = new RecurrentPayment($authorizeNow);

        $this->setRecurrentPayment($recurrentPayment);

        return $recurrentPayment;
    }

    /**
     * @return $this
     */
    public function pix()
    {
        $this->setType(self::PAYMENTTYPE_PIX);
        $this->setCapture(true);

        return $this;
    }

    public function getServiceTaxAmount()
    {
        return $this->serviceTaxAmount;
    }

    /**
     * @return $this
     */
    public function setServiceTaxAmount($serviceTaxAmount)
    {
        $this->serviceTaxAmount = $serviceTaxAmount;

        return $this;
    }

    public function getInstallments()
    {
        return $this->installments;
    }

    /**
     * @return $this
     */
    public function setInstallments($installments)
    {
        $this->installments = $installments;

        return $this;
    }

    public function getInterest()
    {
        return $this->interest;
    }

    /**
     * @return $this
     */
    public function setInterest($interest)
    {
        $this->interest = $interest;

        return $this;
    }

    /**
     * @return bool
     */
    public function getCapture()
    {
        return $this->capture;
    }

    /**
     * @return $this
     */
    public function setCapture($capture)
    {
        $this->capture = $capture;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAuthenticate()
    {
        return $this->authenticate;
    }

    /**
     * @return $this
     */
    public function setAuthenticate($authenticate)
    {
        $this->authenticate = $authenticate;

        return $this;
    }

    public function getRecurrent()
    {
        return $this->recurrent;
    }

    /**
     * @return $this
     */
    public function setRecurrent($recurrent)
    {
        $this->recurrent = $recurrent;

        return $this;
    }

    public function getRecurrentPayment()
    {
        return $this->recurrentPayment;
    }

    /**
     * @return $this
     */
    public function setRecurrentPayment($recurrentPayment)
    {
        $this->recurrentPayment = $recurrentPayment;

        return $this;
    }

    public function getCreditCard()
    {
        return $this->creditCard;
    }

    /**
     * @return $this
     */
    public function setCreditCard(CreditCard $creditCard)
    {
        $this->creditCard = $creditCard;

        return $this;
    }

    public function getDebitCard()
    {
        return $this->debitCard;
    }

    /**
     * @return $this
     */
    public function setDebitCard($debitCard)
    {
        $this->debitCard = $debitCard;

        return $this;
    }

    public function getAuthenticationUrl()
    {
        return $this->authenticationUrl;
    }

    /**
     * @return $this
     */
    public function setAuthenticationUrl($authenticationUrl)
    {
        $this->authenticationUrl = $authenticationUrl;

        return $this;
    }

    public function getTid()
    {
        return $this->tid;
    }

    /**
     * @return $this
     */
    public function setTid($tid)
    {
        $this->tid = $tid;

        return $this;
    }

    public function getProofOfSale()
    {
        return $this->proofOfSale;
    }

    /**
     * @return $this
     */
    public function setProofOfSale($proofOfSale)
    {
        $this->proofOfSale = $proofOfSale;

        return $this;
    }

    public function getAuthorizationCode()
    {
        return $this->authorizationCode;
    }

    /**
     * @return $this
     */
    public function setAuthorizationCode($authorizationCode)
    {
        $this->authorizationCode = $authorizationCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getSoftDescriptor()
    {
        return $this->softDescriptor;
    }

    /**
     * @return $this
     */
    public function setSoftDescriptor($softDescriptor)
    {
        $this->softDescriptor = $softDescriptor;

        return $this;
    }

    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @return $this
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;

        return $this;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @return $this
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * @return $this
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    public function getReceivedDate()
    {
        return $this->receivedDate;
    }

    /**
     * @return $this
     */
    public function setReceivedDate($receivedDate)
    {
        $this->receivedDate = $receivedDate;

        return $this;
    }

    public function getCapturedAmount()
    {
        return $this->capturedAmount;
    }

    /**
     * @return $this
     */
    public function setCapturedAmount($capturedAmount)
    {
        $this->capturedAmount = $capturedAmount;

        return $this;
    }

    public function getCapturedDate()
    {
        return $this->capturedDate;
    }

    /**
     * @return $this
     */
    public function setCapturedDate($capturedDate)
    {
        $this->capturedDate = $capturedDate;

        return $this;
    }

    public function getVoidedAmount()
    {
        return $this->voidedAmount;
    }

    /**
     * @return $this
     */
    public function setVoidedAmount($voidedAmount)
    {
        $this->voidedAmount = $voidedAmount;

        return $this;
    }

    public function getVoidedDate()
    {
        return $this->voidedDate;
    }

    /**
     * @return $this
     */
    public function setVoidedDate($voidedDate)
    {
        $this->voidedDate = $voidedDate;

        return $this;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    public function getReturnCode()
    {
        return $this->returnCode;
    }

    /**
     * @return $this
     */
    public function setReturnCode($returnCode)
    {
        $this->returnCode = $returnCode;

        return $this;
    }

    public function getReturnMessage()
    {
        return $this->returnMessage;
    }

    /**
     * @return $this
     */
    public function setReturnMessage($returnMessage)
    {
        $this->returnMessage = $returnMessage;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @return $this
     */
    public function setLinks($links)
    {
        $this->links = $links;

        return $this;
    }

    public function getExtraDataCollection()
    {
        return $this->extraDataCollection;
    }

    /**
     * @return $this
     */
    public function setExtraDataCollection($extraDataCollection)
    {
        $this->extraDataCollection = $extraDataCollection;

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

    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return $this
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    public function getBoletoNumber()
    {
        return $this->boletoNumber;
    }

    /**
     * @return $this
     */
    public function setBoletoNumber($boletoNumber)
    {
        $this->boletoNumber = $boletoNumber;

        return $this;
    }

    public function getBarCodeNumber()
    {
        return $this->barCodeNumber;
    }

    /**
     * @return $this
     */
    public function setBarCodeNumber($barCodeNumber)
    {
        $this->barCodeNumber = $barCodeNumber;

        return $this;
    }

    public function getDigitableLine()
    {
        return $this->digitableLine;
    }

    /**
     * @return $this
     */
    public function setDigitableLine($digitableLine)
    {
        $this->digitableLine = $digitableLine;

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

    public function getAssignor()
    {
        return $this->assignor;
    }

    /**
     * @return $this
     */
    public function setAssignor($assignor)
    {
        $this->assignor = $assignor;

        return $this;
    }

    public function getDemonstrative()
    {
        return $this->demonstrative;
    }

    /**
     * @return $this
     */
    public function setDemonstrative($demonstrative)
    {
        $this->demonstrative = $demonstrative;

        return $this;
    }

    public function getIdentification()
    {
        return $this->identification;
    }

    /**
     * @return $this
     */
    public function setIdentification($identification)
    {
        $this->identification = $identification;

        return $this;
    }

    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * @return $this
     */
    public function setInstructions($instructions)
    {
        $this->instructions = $instructions;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAcquirerTransactionId()
    {
        return $this->acquirerTransactionId;
    }

    /**
     * @return $this
     */
    public function setAcquirerTransactionId($acquirerTransactionId)
    {
        $this->acquirerTransactionId = $acquirerTransactionId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getQrcodeBase64Image()
    {
        return $this->qrcodeBase64Image;
    }

    /**
     * @return $this
     */
    public function setQrcodeBase64Image($qrcodeBase64Image)
    {
        $this->qrcodeBase64Image = $qrcodeBase64Image;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getQrCodeString()
    {
        return $this->qrCodeString;
    }

    /**
     * @return $this
     */
    public function setQrCodeString($qrCodeString)
    {
        $this->qrCodeString = $qrCodeString;

        return $this;
    }
}
