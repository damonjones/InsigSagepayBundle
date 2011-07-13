<?php

namespace Insig\SagepayBundle\TransactionRegistration;

/**
 * DeferredRequest
 *
 * @author Damon Jones
 */

class DeferredRequest extends Request
{
    public function __construct()
    {
        parent::__construct();
        $this->txType = 'DEFERRED';
    }
}
