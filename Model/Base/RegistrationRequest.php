<?php

namespace Insig\SagepayBundle\Model\Base;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Registration Request
 *
 * @author Damon Jones
 */
abstract class RegistrationRequest
{
    // Numeric. Fixed 4 characters.
    /**
     * @Assert\MinLength(4)
     * @Assert\MaxLength(4)
     * @Assert\Regex("{^\d\.\d\d$}")
     */
    protected $vpsProtocol;

    // Alphabetic. Max 15 characters.
    /**
     * @Assert\NotBlank()
     */
    protected $txType;

    // Alphanumeric. Max 15 characters.
    /**
     * @Assert\MaxLength(15)
     * @Assert\Regex("{^[\w\d-]+$}")
     */
    protected $vendor;

    // Alphanumeric. Max 40 characters.
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(40)
     * @Assert\Regex("{^[\w\d\{\}\.-]+$}")
     */
    protected $vendorTxCode;

    // public API ------------------------------------------------------------

    public function __construct()
    {
        $this->setVendorTxCode($this->generateRandomHash());
    }

    public function getVpsProtocol()
    {
        return $this->vpsProtocol;
    }

    public function setVpsProtocol($value)
    {
        $this->vpsProtocol = number_format($value, 2);

        return $this;
    }

    public function getTxType()
    {
        return $this->txType;
    }

    public function setTxType($value)
    {
        $this->txType = $value;

        return $this;
    }

    public function getVendor()
    {
        return $this->vendor;
    }

    public function setVendor($value)
    {
        $this->vendor = $value;

        return $this;
    }

    public function getVendorTxCode()
    {
        return $this->vendorTxCode;
    }

    public function setVendorTxCode($value)
    {
        $this->vendorTxCode = $value;

        return $this;
    }

    // Validation ------------------------------------------------------------

    /**
     * Checks that all the required properties are set
     *
     * @Assert\True(message="Some required properties are missing")
     */
    public function isComplete()
    {
        $missingProperties = array_diff($this->getRequiredProperties(), array_keys($this->toArray()));

        return empty($missingProperties);
    }

    // output ----------------------------------------------------------------

    /**
     * Return an array of required properties
     *
     * @return array
     * @author Damon Jones
     */
    abstract public function getRequiredProperties();

    /**
     * Returns an associative array of properties
     *
     * Keys are in the correct Sagepay naming format
     * Any values which could contain accented characters should be converted
     * from UTF-8 to ISO-8859-1
     * Empty keys should be removed
     *
     * @return array
     * @author Damon Jones
     */
    abstract public function toArray();

    public function __toString()
    {
        return json_encode($this->toArray());
    }

    protected function generateRandomHash()
    {
        return md5(uniqid(rand(), true));
    }
}
