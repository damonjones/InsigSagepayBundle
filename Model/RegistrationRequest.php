<?php

namespace Insig\SagepayBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Request
 *
 * @author Damon Jones
 */

abstract class RegistrationRequest
{
    // Numeric. Fixed 4 characters.
    /**
     * @Assert\MinLength(4)
     * @Assert\MaxLength(4)
     * @Assert\Regex("{^\d\.\d\d$}")
     */
    protected $vpsProtocol;

    // Alphabetic. Max 15 characters.
    /**
     * @Assert\NotBlank()
     */
    protected $txType;

    // Alphanumeric. Max 15 characters.
    /**
     * @Assert\MaxLength(15)
     * @Assert\Regex("{^[\w\d-]+$}")
     */
    protected $vendor;

    // Alphanumeric. Max 40 characters.
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(40)
     * @Assert\Regex("{^[\w\d\{\}\.-]+$}")
     */
    protected $vendorTxCode;

    // Alphabetic. 3 characters. ISO 4217
    /**
     * @Assert\NotBlank()
     * @Assert\Choice(callback = {"Insig\SagepayBundle\Model\Util",
     * "getCurrencyCodes"})
     */
    protected $currency;

    // Alphanumeric. Max 255 characters. RFC 1738
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(255)
     * @Assert\Url(protocols = {"http", "https"})
     */
    protected $notificationUrl;

    // Optional. Alphabetic. Max 10 characters.
    /**
     * @Assert\Choice({"NORMAL", "LOW"})
     */
    protected $profile;

    // public API ------------------------------------------------------------

    public function __construct()
    {
        $this->setVendorTxCode(md5(uniqid(rand(), true)));
    }

    public function getVpsProtocol()
    {
        return $this->vpsProtocol;
    }

    public function getTxType()
    {
        return $this->txType;
    }

    public function getVendor()
    {
        return $this->vendor;
    }

    public function getVendorTxCode()
    {
        return $this->vendorTxCode;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getNotificationURL()
    {
        return $this->notificationUrl;
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function setVpsProtocol($value)
    {
        $this->vpsProtocol = number_format($value, 2);
    }

    public function setTxType($value)
    {
        $this->txType = $value;
    }

    public function setVendor($value)
    {
        $this->vendor = $value;
    }

    public function setVendorTxCode($value)
    {
        $this->vendorTxCode = $value;
    }

    public function setCurrency($value)
    {
        $this->currency = $value;
    }

    public function setNotificationURL($value)
    {
        $this->notificationUrl = $value;
    }

    public function setProfile($value)
    {
        $this->profile = $value;
    }

    // Validation ------------------------------------------------------------

    /**
     * Checks that all the required properties are set
     *
     * @Assert\True
     */
    public function isComplete()
    {
        return 0 === count(
            array_diff($this->getRequiredProperties(), array_keys($this->toArray()))
        );
    }

    // output ----------------------------------------------------------------

    /**
     * Return an array of required properties
     *
     * @return array
     * @author Damon Jones
     */
    abstract public function getRequiredProperties();

    /**
     * Returns an associative array of properties
     *
     * Keys are in the correct Sagepay naming format
     * Any values which could contain accented characters should be converted
     * from UTF-8 to ISO-8859-1
     * Empty keys should be removed
     *
     * @return array
     * @author Damon Jones
     */
    abstract public function toArray();

    public function __toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * Returns a query string from the object's properties
     *
     * @return string
     * @author Damon Jones
     */
    public function getQueryString()
    {
        return http_build_query($this->toArray());
    }
}