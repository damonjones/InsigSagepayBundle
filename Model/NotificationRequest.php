<?php

namespace Insig\SagepayBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Implemented according to the Sagepay Server Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * A3: Notification of Results for Transactions
 * The Sage Pay server will send notification in the request part of a POST
 * to the Notification URL provided in A1. The request will be URL encoded
 * with Name=Value fields separated by '&' characters.
 *
 * @author Damon Jones
 */

abstract class NotificationRequest
{
    // Numeric. Fixed 4 characters.
    /**
     * @Assert\MinLength(4)
     * @Assert\MaxLength(4)
     * @Assert\Regex("{^\d\.\d\d$}")
     */
    protected $vpsProtocol;

    // Alphabetic. Max 20 characters.
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(20)
     */
    protected $txType;

    // Alphanumeric. Max 40 characters.
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(40)
     * @Assert\Regex("{^[\w\d\{\}\.-]+$}")
     */
    protected $vendorTxCode;

    // Alphanumeric. 38 characters.
    /**
     * @Assert\MaxLength(38)
     */
    protected $vpsTxId;

    // Alphabetic. Max 20 characters.
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(20)
     */
    protected $status;

    // Alphanumeric. Max 255 characters.
    /**
     * @Assert\MaxLength(255)
     */
    protected $statusDetail;

    // Alphabetic. Max 15 characters.
    // "VISA", "MC", "DELTA", "MAESTRO", "UKE", "AMEX", "DC", "JCB", "LASER",
    // "PAYPAL"
    /**
     * @Assert\Choice({"", "VISA", "MC", "DELTA", "MAESTRO", "UKE", "AMEX", "DC",
     * "JCB", "LASER", "PAYPAL"})
     */
    protected $cardType;

    // Numeric. Max 4 Characters.
    /**
     * @Assert\Regex("/^\d{4}$/")
     */
    protected $last4Digits;

    /**
     * @Assert\MaxLength(100)
     */
    protected $vpsSignature;

    // public API ------------------------------------------------------------
    public function __construct($data)
    {
        parse_str($data, $arr);

        $this->vpsProtocol          = $arr['VPSProtocol'];
        $this->txType               = $arr['TxType'];
        $this->vendorTxCode         = $arr['VendorTxCode'];
        $this->vpsTxId              = $arr['VPSTxId'];
        $this->status               = $arr['Status'];
        if (array_key_exists('StatusDetail', $arr)) {
            $this->statusDetail     = $arr['StatusDetail'];
        }
        $this->cardType             = $arr['CardType'];
        $this->last4Digits          = $arr['Last4Digits'];
        $this->vpsSignature         = $arr['VPSSignature'];
    }

    public function getVpsProtocol()
    {
        return $this->vpsProtocol;
    }

    public function getTxType()
    {
        return $this->txType;
    }

    public function getVendorTxCode()
    {
        return $this->vendorTxCode;
    }

    public function getVpsTxId()
    {
        return $this->vpsTxId;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getStatusDetail()
    {
        return $this->statusDetail;
    }

    public function getCardType()
    {
        return $this->cardType;
    }

    public function getLast4Digits()
    {
        return $this->last4Digits;
    }

    public function getVpsSignature()
    {
        return $this->vpsSignature;
    }

    abstract public function getComputedSignature($vendor, $securityKey);

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
    abstract public function toArray();
    
    public function __toString()
    {
        return json_encode($this->toArray());
    }
}