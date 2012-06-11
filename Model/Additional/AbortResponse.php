<?php

namespace Insig\SagepayBundle\Model\Additional;

use Insig\SagepayBundle\Model\Base\RegistrationResponse as BaseRegistrationResponse;

/**
 * Abort Response
 *
 * Implemented according to the Sagepay Server Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * A4: Server response to the abort POST
 * This is the plain text response part of the POST originated by your
 * servers in A1. Encoding will be as Name=Value pairs separated by carriage
 * return and linefeeds (CRLF).
 *
 * @author Damon Jones
 */
class AbortResponse extends BaseRegistrationResponse
{
}
