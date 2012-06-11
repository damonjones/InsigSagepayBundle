<?php

namespace Insig\SagepayBundle\Model\Token\Removal;

use Symfony\Component\Validator\Constraints as Assert;

use Insig\SagepayBundle\Model\Base\RegistrationRequest as BaseRegistrationRequest;

/**
 * Token Removal Request
 *
 * Implemented according to the Token System Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * C1: Token Removal Request
 * This is performed via a HTTPS POST request, sent to
 * https://(live|test).sagepay.com/gateway/service/removetoken.vsp.
 * The details should be URL encoded Name=Value fields separated by
 * '&' characters.
 *
 * @author Damon Jones
 */
class Request extends BaseRegistrationRequest
{
    // Alphabetic. Max 15 characters.
    // "REMOVETOKEN" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"REMOVETOKEN"})
     */
    protected $txType = 'REMOVETOKEN';

    // Alphanumeric. 38 characters.
    /**
     * @Assert\MaxLength(38)
     */
    protected $token;

    public function getToken()
    {
        return $this->token;
    }

    public function setToken($value)
    {
        $this->token = $value;

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
            'Token',
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
            'Token'                 => $this->token
        ));
    }
}
