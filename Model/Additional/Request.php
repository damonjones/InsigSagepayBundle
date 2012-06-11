<?php

namespace Insig\SagepayBundle\Model\Additional;

use Symfony\Component\Validator\Constraints as Assert;

use Insig\SagepayBundle\Model\Base\RegistrationRequest as BaseRegistrationRequest;
use Insig\SagepayBundle\Model\Transaction\TransactionInterface;

/**
 * Request
 *
 * @author Damon Jones
 */
class Request extends BaseRegistrationRequest
{
    // Alphanumeric. 38 characters.
    /**
     * @Assert\MaxLength(38)
     */
    protected $vpsTxId;

    // Alphanumeric. Max 10 characters.
    /**
     * @Assert\MaxLength(10)
     */
    protected $securityKey;

    // Long Integer.
    /**
     * @Assert\Type("integer")
     */
    protected $txAuthNo;

    // public API ------------------------------------------------------------

    public function __construct(TransactionInterface $transaction)
    {
        parent::__construct();

        $this->vendorTxCode = $transaction->getVendorTxCode();
        $this->vpsTxId      = $transaction->getVpsTxId();
        $this->securityKey  = $transaction->getSecurityKey();
        $this->txAuthNo     = $transaction->getTxAuthNo();
    }

    public function getVpsTxId()
    {
        return $this->vpsTxId;
    }

    public function setVpsTxId($vpsTxId)
    {
        $this->vpsTxId = $vpsTxId;

        return $this;
    }

    public function getSecurityKey()
    {
        return $this->securityKey;
    }

    public function setSecurityKey($securityKey)
    {
        $this->securityKey = $securityKey;

        return $this;
    }

    public function getTxAuthNo()
    {
        return $this->txAuthNo;
    }

    public function setTxAuthNo($txAuthNo)
    {
        $this->txAuthNo = (int) $txAuthNo;

        return $this;
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
            'VPSTxId',
            'SecurityKey',
            'TxAuthNo'
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
            'VPSTxId'               => $this->vpsTxId,
            'SecurityKey'           => $this->securityKey,
            'TxAuthNo'              => $this->txAuthNo
        ));
    }
}
