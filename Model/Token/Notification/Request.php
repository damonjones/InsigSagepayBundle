<?php

namespace Insig\SagepayBundle\Model\Token\Notification;

use Symfony\Component\Validator\Constraints as Assert;

use Insig\SagepayBundle\Model\Base\NotificationRequest as BaseNotificationRequest;

/**
 * Implemented according to the Token System Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * A3: Notification of status for Token registration
 * The Sage Pay server will send notification in the request part of a POST
 * to the Notification URL provided in A1. The request will be URL encoded
 * with Name=Value fields separated by '&' characters.
 *
 * @author Damon Jones
 */
class Request extends BaseNotificationRequest
{
    // Alphabetic. Max 15 characters.
    // "TOKEN" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"TOKEN"})
     */
    protected $txType;

    // Alphanumeric. 38 characters.
    /**
     * @Assert\MaxLength(38)
     */
    protected $token;

    // Alphabetic. Max 15 characters.
    // "OK" or "ERROR" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"OK", "ERROR"})
     */
    protected $status;

    // Numeric. Max 4 characters.
    // In MMYY format
    /**
     * @Assert\Regex("/^\d{4}$/")
     */
    protected $expiryDate;

    // public API ------------------------------------------------------------
    public function __construct($arr)
    {
        parent::__construct($arr);

        $this->token                = $arr['Token'];
        $this->expiryDate           = $arr['ExpiryDate'];
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    public function getComputedSignature($vendor, $securityKey)
    {
        return strtoupper(
            md5(
                $this->getVpsTxId() .
                $this->getVendorTxCode() .
                $this->getStatus() .
                $vendor .
                $this->getToken() .
                $securityKey
            )
        );
    }

    /**
     * toArray
     *
     * Returns an associative array of properties
     * Keys are in the correct Sagepay naming format
     * Empty keys are removed
     *
     * @return array
     * @author Damon Jones
     */
    public function toArray()
    {
        return array_filter(
            array(
                'VPSProtocol'       => $this->vpsProtocol,
                'TxType'            => $this->txType,
                'VendorTxCode'      => $this->vendorTxCode,
                'VPSTxId'           => $this->vpsTxId,
                'Status'            => $this->status,
                'CardType'          => $this->cardType,
                'Last4Digits'       => $this->last4Digits,
                'ExpiryDate'        => $this->expiryDate,
                'StatusDetail'      => $this->statusDetail,
                'VPSSignature'      => $this->vpsSignature
            )
        );
    }
}
