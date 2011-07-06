<?php

namespace Insig\SagepayBundle\TransactionRegistration;

class AuthenticateRequest extends Request
{
    public function __construct()
    {
        parent::__construct();
        $this->txType = 'AUTHENTICATE';
    }
}
