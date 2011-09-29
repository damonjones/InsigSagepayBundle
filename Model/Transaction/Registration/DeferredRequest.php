<?php

namespace Insig\SagepayBundle\Model\Transaction\Registration;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Deferred Request
 *
 * @author Damon Jones
 */

class DeferredRequest extends Request
{
    // Alphabetic. Max 15 characters.
    // "DEFERRED" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"DEFERRED"})
     */
    protected $txType = 'DEFERRED';
}
