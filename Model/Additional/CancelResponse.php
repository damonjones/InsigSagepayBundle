<?php

namespace Insig\SagepayBundle\Model\Additional;

use Insig\SagepayBundle\Model\Base\RegistrationResponse as BaseRegistrationResponse;

/**
 * Cancel Response
 *
 * Implemented according to the Sagepay Server Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * A18: Server response to the cancel POST
 * This is the plain text response part of the POST originated by your
 * servers in A1. Encoding will be as Name=Value pairs separated by carriage
 * return and linefeeds (CRLF).
 *
 * @author Damon Jones
 */
class CancelResponse extends BaseRegistrationResponse
{
}
