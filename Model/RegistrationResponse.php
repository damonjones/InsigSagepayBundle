<?php

namespace Insig\SagepayBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Registration Response
 *
 * Implemented according to the Sagepay Server Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * A2: Server response to the transaction registration POST
 * This is the plain text response part of the POST originated by your
 * servers in A1. Encoding will be as Name=Value pairs separated by carriage
 * return and linefeeds (CRLF).
 *
 * @author Damon Jones
 */

abstract class RegistrationResponse
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
    protected $status;

    // Alphanumeric. Max 255 characters.
    /**
     * @Assert\MaxLength(255)
     */
    protected $statusDetail;

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

    /**
     * Accepts a response string consisting of key/value pairs
     * Each key value pair is separated by an equals sign (key=value)
     * Pairs are separated by CRLF
     */
    public function __construct($data)
    {
        parse_str(str_replace("\r\n", '&', $data), $arr);

        $this->vpsProtocol  = $arr['VPSProtocol'];
        $this->status       = $arr['Status'];
        $this->statusDetail = $arr['StatusDetail'];
        if (array_key_exists('VPSTxId', $arr)) {
            $this->vpsTxId      = $arr['VPSTxId'];
        }
        if (array_key_exists('SecurityKey', $arr)) {
            $this->securityKey  = $arr['SecurityKey'];
        }
        if (array_key_exists('NextURL', $arr)) {
            $this->nextUrl      = $arr['NextURL'];
        }
    }

    public function getVpsProtocol()
    {
        return $this->vpsProtocol;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getStatusDetail()
    {
        return $this->statusDetail;
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

    // output ----------------------------------------------------------------

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

    public function __toString()
    {
        return json_encode($this->toArray());
    }
}