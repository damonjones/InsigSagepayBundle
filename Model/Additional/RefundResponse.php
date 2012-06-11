<?php

namespace Insig\SagepayBundle\Model\Additional;

use Insig\SagepayBundle\Model\Base\RegistrationResponse as BaseRegistrationResponse;

/**
 * Refund Response
 *
 * Implemented according to the Sagepay Server Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * A6: Server response to the refund POST
 * This is the plain text response part of the POST originated by your
 * servers in A1. Encoding will be as Name=Value pairs separated by carriage
 * return and linefeeds (CRLF).
 *
 * @author Damon Jones
 */
class RefundResponse extends BaseRegistrationResponse
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

    public function __construct(array $arr)
    {
        parent::__construct($arr);

        if ('OK' === $this->status) {
            $this->vpsTxId  = $arr['VPSTxId'];
            $this->txAuthNo = $arr['TxAuthNo'];
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
}
