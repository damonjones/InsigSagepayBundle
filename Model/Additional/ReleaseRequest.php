<?php

namespace Insig\SagepayBundle\Model\Additional;

use Symfony\Component\Validator\Constraints as Assert;

use Insig\SagepayBundle\Model\Transaction\TransactionInterface;

/**
 * Release Request
 *
 * Implemented according to the Sagepay Server Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * A1: Releasing a DEFERRED or REPEATDEFERRED Payment
 * This is performed via a HTTPS POST request, sent to the initial
 * Sage Pay Payment URL server release.vsp. The details should be
 * URL encoded Name=Value fields separated by '&' characters.
 *
 * @author Damon Jones
 */
class ReleaseRequest extends Request
{
    // Alphabetic. Max 20 characters.
    // "RELEASE" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"RELEASE"})
     */
    protected $txType = 'RELEASE';

    // Numeric. 0.01 to 100,000.00
    /**
     * @Assert\NotBlank()
     * @Assert\Min(0.01)
     * @Assert\Max(100000.0)
     */
    protected $releaseAmount;

    // public API ------------------------------------------------------------

    public function __construct(TransactionInterface $transaction)
    {
        parent::__construct($transaction);

        $this->releaseAmount = $transaction->getAmount();
    }

    public function getReleaseAmount()
    {
        return $this->releaseAmount;
    }

    public function setReleaseAmount($value)
    {
        $this->releaseAmount = (float) $value;

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
        $arr = parent::getRequiredProperties();
        $arr[] = 'ReleaseAmount';

        return $arr;
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
        $arr = parent::toArray();
        $arr['ReleaseAmount'] = number_format($this->releaseAmount, 2);

        return array_filter($arr);
    }
}
