<?php

namespace Insig\SagepayBundle\Model\Additional;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Abort Request
 *
 * Implemented according to the Sagepay Server Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * A3: Aborting a DEFERRED Payment
 * This is performed via a HTTPS POST request, sent to the initial
 * Sage Pay Payment URL server abort.vsp. The details should be
 * URL encoded Name=Value fields separated by '&' characters.
 *
 * @author Damon Jones
 */
class AbortRequest extends Request
{
    // Alphabetic. Max 20 characters.
    // "ABORT" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"ABORT"})
     */
    protected $txType = 'ABORT';
}
