<?php

namespace Insig\SagepayBundle\TransactionRegistration;

/**
 * Authenticate Request
 *
 * @author Damon Jones
 */

class AuthenticateRequest extends Request
{
    public function __construct()
    {
        parent::__construct();
        $this->txType = 'AUTHENTICATE';
    }
}
