<?php

namespace Insig\SagepayBundle\Model\Token\Registration;

use Symfony\Component\Validator\Constraints as Assert;

use Insig\SagepayBundle\Model\RegistrationRequest;

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

class Request extends RegistrationRequest
{
    // Alphabetic. Max 15 characters.
    // "TOKEN" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"TOKEN"})
     */
    protected $txType = 'TOKEN';

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