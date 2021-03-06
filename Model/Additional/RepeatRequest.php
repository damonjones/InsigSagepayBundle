<?php

namespace Insig\SagepayBundle\Model\Additional;

use Symfony\Component\Validator\Constraints as Assert;

use Insig\SagepayBundle\Model\Transaction\TransactionInterface;

/**
 * Repeat Request
 *
 * Implemented according to the Sagepay Server Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * A7: Repeat payment registration
 * This is performed via a HTTPS POST request, sent to the initial
 * Sage Pay Payment URL server repeat.vsp. The details should be
 * URL encoded Name=Value fields separated by '&' characters.
 *
 * @author Damon Jones
 */
class RepeatRequest extends Request
{
    // Alphabetic. Max 20 characters.
    // "REPEAT" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"REPEAT"})
     */
    protected $txType = 'REPEAT';

    // Numeric. 0.01 to 100,000.00
    /**
     * @Assert\NotBlank()
     * @Assert\Min(0.01)
     * @Assert\Max(100000.0)
     */
    protected $amount;

    // Alphabetic. 3 characters. ISO 4217
    /**
     * @Assert\NotBlank()
     * @Assert\Choice(callback = {"Insig\SagepayBundle\Model\Util",
     * "getCurrencyCodes"})
     */
    protected $currency;

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

    // Numeric. 3 or 4 characters.
    /**
     * @Assert\MinLength(3)
     * @Assert\MaxLength(4)
     * @Assert\Regex("{^\d{3,4}$}")
     */
    protected $cv2;

    // public API ------------------------------------------------------------

    public function __construct(TransactionInterface $transaction, $amount, $currency, $description, $cv2 = null)
    {
        $this->setVendorTxCode($this->generateRandomHash());

        $this->amount               = (float) $amount;
        $this->currency             = $currency;
        $this->description          = $description;
        $this->relatedVpsTxId       = $transaction->getVpsTxId();
        $this->relatedVendorTxCode  = $transaction->getVendorTxCode();
        $this->relatedSecurityKey   = $transaction->getSecurityKey();
        $this->relatedTxAuthNo      = $transaction->getTxAuthNo();
        $this->cv2                  = $cv2;
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
            'Currency',
            'Description',
            'RelatedVPSTxId',
            'RelatedVendorTxCode',
            'RelatedSecurityKey',
            'RelatedTxAuthNo'
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
            'Currency'              => $this->currency,
            'Description'           => $this->description,
            'RelatedVPSTxId'        => $this->relatedVpsTxId,
            'RelatedVendorTxCode'   => $this->relatedVendorTxCode,
            'RelatedSecurityKey'    => $this->relatedSecurityKey,
            'RelatedTxAuthNo'       => $this->relatedTxAuthNo,
            'Cv2'                   => $this->cv2
        ));
    }
}
