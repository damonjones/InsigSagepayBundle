<?php

namespace Insig\SagepayBundle\Model\Transaction\Registration;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Authenticate Request
 *
 * @author Damon Jones
 */
class AuthenticateRequest extends Request
{
    // Alphabetic. Max 15 characters.
    // "AUTHENTICATE" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"AUTHENTICATE"})
     */
    protected $txType = 'AUTHENTICATE';
}
