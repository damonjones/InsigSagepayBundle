<?php

namespace Insig\SagepayBundle\Model\Transaction\Notification;

use Symfony\Component\Validator\Constraints as Assert;

use Insig\SagepayBundle\Model\Base\NotificationRequest as BaseNotificationRequest;

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
class Request extends BaseNotificationRequest
{
    // Alphabetic. Max 20 characters.
    // "PAYMENT", "DEFERRED" or "AUTHENTICATE" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"PAYMENT", "DEFERRED", "AUTHENTICATE"})
     */
    protected $txType;

    // Alphabetic. Max 20 characters.
    // "OK", "NOTAUTHED", "ABORT", "REJECTED", "AUTHENTICATED", "REGISTERED"
    // or "ERROR" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"OK", "NOTAUTHED", "ABORT", "REJECTED",
     * "AUTHENTICATED", "REGISTERED", "ERROR"})
     */
    protected $status;

    // Long Integer.
    // Only present if the transaction was successfully authorised
    // (Status = "OK")
    /**
     * @Assert\Type("integer")
     */
    protected $txAuthNo;

    // Alphabetic. Max 50 characters.
    // "ALL MATCH", "SECURITY CODE MATCH ONLY", "ADDRESS MATCH ONLY",
    // "NO DATA MATCHES" or "DATA NOT CHECKED" ONLY.
    // Not present if the Status is "AUTHENTICATED" or "REGISTERED".
    /**
     * @Assert\Choice({"", "ALL MATCH", "SECURITY CODE MATCH ONLY",
     * "ADDRESS MATCH ONLY", "NO DATA MATCHES", "DATA NOT CHECKED"})
     */
    protected $avsCv2;

    // Alphabetic. Max 20 characters.
    // "NOTPROVIDED", "NOTCHECKED", "MATCHED" or "NOTMATCHED" ONLY.
    // Not present if the Status is "AUTHENTICATED" or "REGISTERED".
    /**
     * @Assert\Choice({"", "NOTPROVIDED", "NOTCHECKED", "MATCHED", "NOTMATCHED"})
     */
    protected $addressResult;

    // Alphabetic. Max 20 characters.
    // "NOTPROVIDED", "NOTCHECKED", "MATCHED" or "NOTMATCHED" ONLY.
    // Not present if the Status is "AUTHENTICATED" or "REGISTERED".
    /**
     * @Assert\Choice({"", "NOTPROVIDED", "NOTCHECKED", "MATCHED", "NOTMATCHED"})
     */
    protected $postCodeResult;

    // Alphabetic. Max 20 characters.
    // "NOT PROVIDED", "NOT CHECKED", "MATCHED" or "NOT MATCHED" ONLY.
    // Not present if the Status is "AUTHENTICATED" or "REGISTERED".
    /**
     * @Assert\Choice({"", "NOTPROVIDED", "NOTCHECKED", "MATCHED", "NOTMATCHED"})
     */
    protected $cv2Result;

    // Flag
    /**
     * @Assert\Min(0)
     * @Assert\Max(1)
     */
    protected $giftAid;

    // Alphabetic. Max 50 characters.
    // "OK", "NOTCHECKED", "NOTAVAILABLE", "NOTAUTHED", "INCOMPLETE" or
    // "ERROR" ONLY
    /**
     * @Assert\Choice({"", "OK", "NOTCHECKED", "NOTAVAILABLE", "NOTAUTHED",
     * "INCOMPLETE", "ERROR"})
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
    public function __construct($arr)
    {
        parent::__construct($arr);

        if ('OK' === $this->status) {
            $this->txAuthNo         = (int) $arr['TxAuthNo'];
        }

        if ('AUTHENTICATED' !== $this->status && 'REGISTERED' !== $this->status) {
            $this->avsCv2           = $arr['AVSCV2'];
            $this->addressResult    = $arr['AddressResult'];
            $this->postCodeResult   = $arr['PostCodeResult'];
            $this->cv2Result        = $arr['CV2Result'];
        }
        $this->giftAid              = $arr['GiftAid'];
        $this->threeDSecureStatus   = $arr['3DSecureStatus'];
        if ('OK' === $this->threeDSecureStatus) {
            $this->cavv             = $arr['CAVV'];
        }
        // PayPal transactions only
        if (array_key_exists('AddressStatus', $arr)) {
            $this->addressStatus    = $arr['AddressStatus'];
        }
        if (array_key_exists('PayerStatus', $arr)) {
            $this->payerStatus      = $arr['PayerStatus'];
        }
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

    public function getComputedSignature($vendor, $securityKey)
    {
        return strtoupper(
            md5(
                $this->getVpsTxId() .
                $this->getVendorTxCode() .
                $this->getStatus() .
                $this->getTxAuthNo() .
                $vendor .
                $this->getAvsCv2() .
                $securityKey .
                $this->getAddressResult() .
                $this->getPostCodeResult() .
                $this->getCv2Result() .
                $this->getGiftAid() .
                $this->get3dSecureStatus() .
                $this->getCavv() .
                $this->getAddressStatus() .
                $this->getPayerStatus() .
                $this->getCardType() .
                $this->getLast4Digits()
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
