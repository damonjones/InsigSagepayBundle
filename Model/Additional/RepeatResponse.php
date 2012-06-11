<?php

namespace Insig\SagepayBundle\Model\Additional;

use Insig\SagepayBundle\Model\Base\RegistrationResponse as BaseRegistrationResponse;

/**
 * Repeat Response
 *
 * Implemented according to the Sagepay Server Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * A8: Server response to the refund POST
 * This is the plain text response part of the POST originated by your
 * servers in A1. Encoding will be as Name=Value pairs separated by carriage
 * return and linefeeds (CRLF).
 *
 * @author Damon Jones
 */
class RepeatResponse extends BaseRegistrationResponse
{
    // Alphanumeric. 38 characters.
    /**
     * @Assert\MaxLength(38)
     */
    protected $vpsTxId;

    // Long Integer.
    // Only present if the transaction was successfully authorised
    // (Status = "OK")
    /**
     * @Assert\Type("integer")
     */
    protected $txAuthNo;

    // Alphanumeric. Max 10 characters.
    /**
     * @Assert\MaxLength(10)
     */
    protected $securityKey;


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

    public function __construct(array $arr)
    {
        parent::__construct($arr);

        if ('OK' === $this->status) {
            $this->vpsTxId     = $arr['VPSTxId'];
            $this->txAuthNo    = $arr['TxAuthNo'];
            $this->securityKey = $arr['SecurityKey'];
        }

        if (array_key_exists('AVSCV2', $arr)) {
            $this->avsCv2           = $arr['AVSCV2'];
            $this->addressResult    = $arr['AddressResult'];
            $this->postCodeResult   = $arr['PostCodeResult'];
            $this->cv2Result        = $arr['CV2Result'];
        }
    }

    public function getVpsTxId()
    {
        return $this->vpsTxId;
    }

    public function getTxAuthNo()
    {
        return $this->txAuthNo;
    }

    public function getSecurityKey()
    {
        return $this->securityKey;
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
}
