<?php

namespace Insig\SagepayBundle\Model\Additional;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Cancel Request
 *
 * Implemented according to the Sagepay Server Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * A17: Cancelling an Authenticated/Registered transaction
 * This is performed via a HTTPS POST request, sent to the initial
 * Sage Pay Payment URL server cancel.vsp. The details should be
 * URL encoded Name=Value fields separated by '&' characters.
 *
 * @author Damon Jones
 */
class CancelRequest extends Request
{
    // Alphabetic. Max 20 characters.
    // "CANCEL" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"CANCEL"})
     */
    protected $txType = 'CANCEL';

    /**
     * Return an array of required properties
     *
     * @return array
     * @author Damon Jones
     */
    public function getRequiredProperties()
    {
        return array(
            'VPSProtocol',
            'TxType',
            'Vendor',
            'VendorTxCode',
            'VPSTxId',
            'SecurityKey'
        );
    }
}
