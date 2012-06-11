<?php

namespace Insig\SagepayBundle\Model\Additional;

use Symfony\Component\Validator\Constraints as Assert;

use Insig\SagepayBundle\Model\Transaction\TransactionInterface;

/**
 * Authorise Request
 *
 * Implemented according to the Sagepay Server Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * A15: Authorising an Authenticated/Registered transaction
 * This is performed via a HTTPS POST request, sent to the initial
 * Sage Pay Payment URL server authorise.vsp. The details should be
 * URL encoded Name=Value fields separated by '&' characters.
 *
 * @author Damon Jones
 */
class AuthoriseRequest extends Request
{
    // Alphabetic. Max 20 characters.
    // "AUTHORISE" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"AUTHORISE"})
     */
    protected $txType = 'AUTHORISE';

    // Numeric. 0.01 to 100,000.00
    /**
     * @Assert\NotBlank()
     * @Assert\Min(0.01)
     * @Assert\Max(100000.0)
     */
    protected $amount;

    // Alphanumeric. Max 100 characters.
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(100)
     */
    protected $description;

    // Alphanumeric. 38 characters.
    /**
     * @Assert\MaxLength(38)
     */
    protected $relatedVpsTxId;

    // Alphanumeric. Max 40 characters.
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(40)
     * @Assert\Regex("{^[\w\d\{\}\.-]+$}")
     */
    protected $relatedVendorTxCode;

    // Alphanumeric. Max 10 characters.
    /**
     * @Assert\MaxLength(10)
     */
    protected $relatedSecurityKey;

    // Long Integer.
    /**
     * @Assert\Type("integer")
     */
    protected $relatedTxAuthNo;

    // Optional. Flag.
    /**
     * @Assert\Min(0)
     * @Assert\Max(3)
     */
    protected $applyAvsCv2;

    // public API ------------------------------------------------------------

    public function __construct(TransactionInterface $transaction, $amount, $description, $applyAvsCv2 = null)
    {
        $this->setVendorTxCode($this->generateRandomHash());

        $this->amount               = (float) $amount;
        $this->description          = $description;
        $this->relatedVpsTxId       = $transaction->getVpsTxId();
        $this->relatedVendorTxCode  = $transaction->getVendorTxCode();
        $this->relatedSecurityKey   = $transaction->getSecurityKey();
        $this->applyAvsCv2          = (int) $applyAvsCv2;
    }

    public function getAmount()
    {
        return $this->amount;
    }

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
            'Amount',
            'Description',
            'RelatedVPSTxId',
            'RelatedVendorTxCode',
            'RelatedSecurityKey'
        );
    }

    /**
     * toArray
     *
     * Returns an associative array of properties
     * Keys are in the correct Sagepay naming format
     * Any values which could contain accented characters are converted
     * from UTF-8 to ISO-8859-1
     * Empty keys are removed
     *
     * @return array
     * @author Damon Jones
     */
    public function toArray()
    {
        return array_filter(array(
            'VPSProtocol'           => $this->vpsProtocol,
            'TxType'                => $this->txType,
            'Vendor'                => $this->vendor,
            'VendorTxCode'          => $this->vendorTxCode,
            'Amount'                => $this->amount,
            'Description'           => $this->description,
            'RelatedVPSTxId'        => $this->relatedVpsTxId,
            'RelatedVendorTxCode'   => $this->relatedVendorTxCode,
            'RelatedSecurityKey'    => $this->relatedSecurityKey,
            'ApplyAvsCv2'           => $this->applyAvsCv2
        ));
    }
}
