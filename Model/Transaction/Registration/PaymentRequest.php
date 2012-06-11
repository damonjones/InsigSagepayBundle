<?php

namespace Insig\SagepayBundle\Model\Transaction\Registration;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Payment Request
 *
 * @author Damon Jones
 */
class PaymentRequest extends Request
{
    // Alphabetic. Max 15 characters.
    // "PAYMENT" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"PAYMENT"})
     */
    protected $txType = 'PAYMENT';
}
