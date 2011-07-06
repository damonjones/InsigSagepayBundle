<?php

namespace Insig\SagepayBundle\TransactionRegistration;

class DeferredRequest extends Request
{
    public function __construct()
    {
        parent::__construct();
        $this->txType = 'DEFERRED';
    }
}
