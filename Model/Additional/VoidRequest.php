<?php

namespace Insig\SagepayBundle\Model\Additional;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Void Request
 *
 * Implemented according to the Sagepay Server Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * A9: Repeat payment registration
 * This is performed via a HTTPS POST request, sent to the initial
 * Sage Pay Payment URL server void.vsp. The details should be
 * URL encoded Name=Value fields separated by '&' characters.
 *
 * @author Damon Jones
 */
class VoidRequest extends Request
{
    // Alphabetic. Max 20 characters.
    // "VOID" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"VOID"})
     */
    protected $txType = 'VOID';
}
