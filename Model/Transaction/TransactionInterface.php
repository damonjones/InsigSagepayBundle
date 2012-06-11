<?php

namespace Insig\SagepayBundle\Model\Transaction;

/**
 * Transaction Interface
 *
 * @author Damon Jones
 */
interface TransactionInterface
{
    function getVendorTxCode();
    function setVendorTxCode($vendorTxCode);

    function getVpsTxId();
    function setVpsTxId($VpsTxId);

    function getSecurityKey();
    function setSecurityKey($securityKey);

    function getTxAuthNo();
    function setTxAuthNo($txAuthNo);

    function getTxType();
    function setTxType($txType);

    function getAmount();
    function setAmount($amount);
}
