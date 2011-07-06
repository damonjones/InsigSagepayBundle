<?php

namespace Insig\SagepayBundle\TransactionRegistration;

class PaymentRequest extends Request
{
    public function __construct()
    {
        parent::__construct();
        $this->txType = 'PAYMENT';
    }
}
