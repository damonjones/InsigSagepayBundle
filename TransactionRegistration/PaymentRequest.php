<?php

namespace Insig\SagepayBundle\TransactionRegistration;

/**
 * PaymentRequest
 *
 * @author Damon Jones
 */

class PaymentRequest extends Request
{
    public function __construct()
    {
        parent::__construct();
        $this->txType = 'PAYMENT';
    }
}
