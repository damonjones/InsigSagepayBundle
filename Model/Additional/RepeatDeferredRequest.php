<?php

namespace Insig\SagepayBundle\Model\Additional;

use Symfony\Component\Validator\Constraints as Assert;

use Insig\SagepayBundle\Model\Transaction\TransactionInterface;

/**
 * Repeat Deferred Request
 *
 * Implemented according to the Sagepay Server Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * A7: Repeat deferred payment registration
 * This is performed via a HTTPS POST request, sent to the initial
 * Sage Pay Payment URL server repeat.vsp. The details should be
 * URL encoded Name=Value fields separated by '&' characters.
 *
 * @author Damon Jones
 */
class RepeatDeferredRequest extends RepeatRequest
{
    // Alphabetic. Max 20 characters.
    // "REPEATDEFERRED" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"REPEATDEFERRED"})
     */
    protected $txType = 'REPEATDEFERRED';
}
