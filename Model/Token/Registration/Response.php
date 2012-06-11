<?php

namespace Insig\SagepayBundle\Model\Token\Registration;

use Symfony\Component\Validator\Constraints as Assert;

use Insig\SagepayBundle\Model\Base\RegistrationResponse as BaseRegistrationResponse;

/**
 * Token Registration Response
 *
 * Implemented according to the Token System Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * A2: Server response to the initial token registration POST
 * This is the plain text response part of the POST originated by your
 * servers in A1. Encoding will be as Name=Value pairs separated by carriage
 * return and linefeeds (CRLF).
 *
 * @author Damon Jones
 */
class Response extends BaseRegistrationResponse
{
    // Alphabetic. Max 15 characters.
    // "OK", "MALFORMED" or "INVALID" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"OK", "MALFORMED", "INVALID"})
     */
    protected $status;

    // Alphanumeric. 38 characters.
    /**
     * @Assert\MaxLength(38)
     */
    protected $vpsTxId;

    // Alphanumeric. Max 10 characters.
    /**
     * @Assert\MaxLength(10)
     */
    protected $securityKey;

    // Alphanumeric. Fully Qualified URL. Max 255 characters.
    /**
     * @Assert\Url
     * @Assert\MaxLength(255)
     */
    protected $nextUrl;

    // public API ------------------------------------------------------------

    public function __construct(array $arr)
    {
        parent::__construct($arr);

        if ('OK' === $this->status || 'OK REPEATED' === $this->status) {
            $this->vpsTxId     = $arr['VPSTxId'];
        }

        if ('OK' === $this->status) {
            $this->securityKey = $arr['SecurityKey'];
            $this->nextUrl     = $arr['NextURL'];
        }
    }

    public function getVpsTxId()
    {
        return $this->vpsTxId;
    }

    public function getSecurityKey()
    {
        return $this->securityKey;
    }

    public function getNextUrl()
    {
        return $this->nextUrl;
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
                'VPSProtocol'   => $this->vpsProtocol,
                'Status'        => $this->status,
                'StatusDetail'  => $this->statusDetail,
                'VPSTxId'       => $this->vpsTxId,
                'SecurityKey'   => $this->securityKey,
                'NextURL'       => $this->nextUrl
            )
        );
    }
}
