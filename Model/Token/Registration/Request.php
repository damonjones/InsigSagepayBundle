<?php

namespace Insig\SagepayBundle\Model\Token\Registration;

use Symfony\Component\Validator\Constraints as Assert;

use Insig\SagepayBundle\Model\Base\RegistrationRequest as BaseRegistrationRequest;

/**
 * Token Registration Request
 *
 * Implemented according to the Token System Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * A1: Token Registration
 * This is performed via a HTTPS POST request, sent to
 * https://(live|test).sagepay.com/gateway/service/token.vsp.
 * The details should be URL encoded Name=Value fields separated by
 * '&' characters.
 *
 * @author Damon Jones
 */
class Request extends BaseRegistrationRequest
{
    // Alphabetic. Max 15 characters.
    // "TOKEN" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"TOKEN"})
     */
    protected $txType = 'TOKEN';

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

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setCurrency($value)
    {
        $this->currency = $value;

        return $this;
    }

    public function getNotificationURL()
    {
        return $this->notificationUrl;
    }

    public function setNotificationURL($value)
    {
        $this->notificationUrl = $value;

        return $this;
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function setProfile($value)
    {
        $this->profile = $value;

        return $this;
    }

    /**
     * Return an array of required properties
     *
     * @return array
     * @author Damon Jones
     */
    public function getRequiredProperties()
    {
        return array(
            'VPSProtocol',
            'TxType',
            'Vendor',
            'VendorTxCode',
            'Currency',
            'NotificationURL',
        );
    }

    /**
     * Returns an associative array of properties
     *
     * Keys are in the correct Sagepay naming format
     * Any values which could contain accented characters are converted
     * from UTF-8 to ISO-8859-1
     * Empty keys are removed
     *
     * @return array
     * @author Damon Jones
     */
    public function toArray()
    {
        return array_filter(array(
            'VPSProtocol'           => $this->vpsProtocol,
            'TxType'                => $this->txType,
            'Vendor'                => $this->vendor,
            'VendorTxCode'          => $this->vendorTxCode,
            'Currency'              => $this->currency,
            'NotificationURL'       => $this->notificationUrl,
            'Profile'               => $this->profile
        ));
    }
}
