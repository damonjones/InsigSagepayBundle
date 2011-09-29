<?php

namespace Insig\SagepayBundle\Model\Transaction\Registration;

use Symfony\Component\Validator\Constraints as Assert;

use Insig\SagepayBundle\Model\RegistrationResponse;

/**
 * Transaction Registration Response
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

class Response extends RegistrationResponse
{
    // Alphabetic. Max 15 characters.
    // "OK", "MALFORMED", "INVALID" or "ERROR" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"OK", "MALFORMED", "INVALID", "ERROR"})
     */
    protected $status;
}