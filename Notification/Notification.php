<?php

namespace Insig\SagepayBundle\Notification;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Implemented according to the Sagepay Server Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * A3: Notification of Results for Transactions
 * The Sage Pay server will send notification in the request part of a POST
 * to the Notification URL provided in A1. The request will be URL encoded
 * with Name=Value fields separated by '&' characters.
 */

class Notification
{
    // Numeric. Fixed 4 characters.
    /**
     * @Assert\MinLength(4)
     * @Assert\MaxLength(4)
     * @Assert\Regex("{^\d\.\d\d$}")
     */
    protected $vpsProtocol;

    // Alphabetic. Max 20 characters.
    // "PAYMENT", "DEFERRED" or "AUTHENTICATE" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"PAYMENT", "DEFERRED", "AUTHENTICATE"})
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
    // "OK", "NOTAUTHED", "ABORT", "REJECTED", "AUTHENTICATED", "REGISTERED"
    // or "ERROR" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"OK", "NOTAUTHED", "ABORT", "REJECTED", "AUTHENTICATED", "REGISTERED", "ERROR"})
     */
    protected $status;

    // Alphanumeric. Max 255 characters.
    /**
     * @Assert\MaxLength(255)
     */
    protected $statusDetail;

    // Long Integer.
    // Only present if the transaction was successfully authorised (Status = "OK")
    /**
     * @Assert\type("integer")
     */
    protected $txAuthNo;

    // Alphabetic. Max 50 characters.
    // "ALL MATCH", "SECURITY CODE MATCH ONLY", "ADDRESS MATCH ONLY", "NO DATA MATCHES" or "DATA NOT CHECKED" ONLY.
    // Not present if the Status is "AUTHENTICATED" or "REGISTERED".
    /**
     * @Assert\Choice({"ALL MATCH", "SECURITY CODE MATCH ONLY", "ADDRESS MATCH ONLY", "NO DATA MATCHES", "DATA NOT CHECKED"})
     */
    protected $avsCv2;

    // Alphabetic. Max 20 characters.
    // "NOT PROVIDED", "NOT CHECKED", "MATCHED" or "NOT MATCHED" ONLY.
    // Not present if the Status is "AUTHENTICATED" or "REGISTERED".
    /**
     * @Assert\Choice({"NOT PROVIDED", "NOT CHECKED", "MATCHED", "NOT MATCHED"})
     */
    protected $addressResult;

    // Alphabetic. Max 20 characters.
    // "NOT PROVIDED", "NOT CHECKED", "MATCHED" or "NOT MATCHED" ONLY.
    // Not present if the Status is "AUTHENTICATED" or "REGISTERED".
    /**
     * @Assert\Choice({"NOT PROVIDED", "NOT CHECKED", "MATCHED", "NOT MATCHED"})
     */
    protected $postCodeResult;

    // Alphabetic. Max 20 characters.
    // "NOT PROVIDED", "NOT CHECKED", "MATCHED" or "NOT MATCHED" ONLY.
    // Not present if the Status is "AUTHENTICATED" or "REGISTERED".
    /**
     * @Assert\Choice({"NOT PROVIDED", "NOT CHECKED", "MATCHED", "NOT MATCHED"})
     */
    protected $cv2Result;

    // Flag
    /**
     * @Assert\Min(0)
     * @Assert\Max(1)
     */
    protected $giftAid;

    // Alphabetic. Max 50 characters.
    // "OK", "NOTCHECKED", "NOTAVAILABLE", "NOTAUTHED", "INCOMPLETE" or "ERROR" ONLY
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"OK", "NOTCHECKED", "NOTAVAILABLE", "NOTAUTHED", "INCOMPLETE", "ERROR"})
     */
    protected $threeDSecureStatus;

    // Alphanumeric. Max 32 characters.
    // Only present if 3DSecureStatus field is "OK"
    /**
     * @Assert\MaxLength(32)
     */
    protected $cavv;

    // Alphabetic. Max 20 characters.
    // PayPal transactions only.
    // "NONE", "CONFIRMED" or "UNCONFIRMED" ONLY.
    /**
     * @Assert\Choice({"NONE", "CONFIRMED", "UNCONFIRMED"})
     */
    protected $addressStatus;

    // Alphabetic. Max 20 characters.
    // PayPal transactions only.
    // "VERIFIED" or "UNVERIFIED" ONLY.
    /**
     * @Assert\Choice({"VERIFIED" , "UNVERIFIED"})
     */
    protected $payerStatus;

    // Alphabetic. Max 15 characters.
    // "VISA", "MC", "DELTA", "MAESTRO", "UKE", "AMEX", "DC", "JCB", "LASER", "PAYPAL"
    /**
     * @Assert\Choice({"VISA", "MC", "DELTA", "MAESTRO", "UKE", "AMEX", "DC", "JCB", "LASER", "PAYPAL"})
     */
    protected $cardType;

    // Numeric. Max 4 Characters.
    /**
     * @Assert\Regex("/^\d{4}$/")
     */
    protected $last4Digits;

    // Alphanumeric. Max 100 characters.
    // MD5 signature (upper case) of:
    // VPSTxId, VendorTxCode, Status, TxAuthNo, VendorName, AVSCV2,
    // SecurityKey, AddressResult, PostCodeResult, CV2Result, GiftAid,
    // 3DSecureStatus, CAVV, AddressStatus, PayerStatus, CardType, Last4Digits
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
        if (in_array('StatusDetail', $arr)) {
            $this->statusDetail     = $arr['StatusDetail'];
        }
        $this->txAuthNo             = (int) $arr['TxAuthNo'];
        $this->avsCv2               = $arr['AVSCV2'];
        $this->addressResult        = $arr['AddressResult'];
        $this->postCodeResult       = $arr['PostCodeResult'];
        $this->cv2Result            = $arr['CV2Result'];
        $this->giftAid              = $arr['GiftAid'];
        $this->threeDSecureStatus   = $arr['3DSecureStatus'];
        $this->cavv                 = $arr['CAVV'];
        if (in_array('AddressStatus', $arr)) {
            $this->addressStatus    = $arr['AddressStatus'];
        }
        if (in_array('PayerStatus', $arr)) {
            $this->payerStatus      = $arr['PayerStatus'];
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

    public function getTxAuthNo()
    {
        return $this->txAuthNo;
    }

    public function getAvsCv2()
    {
        return $this->avsCv2;
    }

    public function getAddressResult()
    {
        return $this->addressResult;
    }

    public function getPostCodeResult()
    {
        return $this->postCodeResult;
    }

    public function getCv2Result()
    {
        return $this->cv2Result;
    }

    public function getGiftAid()
    {
        return $this->giftAid;
    }

    public function get3dSecureStatus()
    {
        return $this->threeDSecureStatus;
    }

    // alias of get3dSecureStatus()
    public function getThreeDSecureStatus()
    {
        return $this->get3dSecureStatus();
    }

    public function getCavv()
    {
        return $this->cavv;
    }

    public function getAddressStatus()
    {
        return $this->addressStatus;
    }

    public function getPayerStatus()
    {
        return $this->payerStatus;
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

    // output ----------------------------------------------------------------
    public function toArray()
    {
        /**
         * Returns an associative array of properties
         * Keys are in the correct Sagepay naming format
         * Empty keys are removed
         */
        return array_filter(
            array(
                'VPSProtocol'       => $this->vpsProtocol,
                'TxType'            => $this->txType,
                'VendorTxCode'      => $this->vendorTxCode,
                'VPSTxId'           => $this->vpsTxId,
                'Status'            => $this->status,
                'StatusDetail'      => $this->statusDetail,
                'TxAuthNo'          => $this->txAuthNo,
                'AVSCV2'            => $this->avsCv2,
                'AddressResult'     => $this->addressResult,
                'PostCodeResult'    => $this->postCodeResult,
                'CV2Result'         => $this->cv2Result,
                'GiftAid'           => $this->giftAid,
                '3DSecureStatus'    => $this->threeDSecureStatus,
                'CAVV'              => $this->cavv,
                'AddressStatus'     => $this->addressStatus,
                'PayerStatus'       => $this->payerStatus,
                'CardType'          => $this->cardType,
                'Last4Digits'       => $this->last4Digits,
                'VPSSignature'      => $this->vpsSignature
            )
        );
    }
}