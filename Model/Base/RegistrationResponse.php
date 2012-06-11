<?php

namespace Insig\SagepayBundle\Model\Base;

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

    // public API ------------------------------------------------------------

    public function __construct(array $arr)
    {
        $this->vpsProtocol  = $arr['VPSProtocol'];
        $this->status       = $arr['Status'];
        $this->statusDetail = $arr['StatusDetail'];
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
                'StatusDetail'  => $this->statusDetail
            )
        );
    }

    public function __toString()
    {
        return json_encode($this->toArray());
    }
}
