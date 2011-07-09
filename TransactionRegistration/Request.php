<?php

namespace Insig\SagepayBundle\TransactionRegistration;

use Symfony\Component\Validator\Constraints as Assert;
use Insig\SagepayBundle\Exception\IllegalOperationException;

/**
 * Implemented according to the Sagepay Server Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * A1: Transaction Registration
 * This is performed via a HTTPS POST request, sent to the initial
 * Sage Pay Payment URL server server-register.vsp. The details should be
 * URL encoded Name=Value fields separated by '&' characters.
 */

abstract class Request
{
    protected static $required = array(
        'VPSProtocol',
        'TxType',
        'Vendor',
        'VendorTxCode',
        'Amount',
        'Currency',
        'Description',
        'NotificationURL',
        'BillingSurname',
        'BillingFirstnames',
        'BillingAddress1',
        'BillingCity',
        'BillingPostCode',
        'BillingCountry',
        'BillingState',
        'DeliverySurname',
        'DeliveryFirstnames',
        'DeliveryAddress1',
        'DeliveryCity',
        'DeliveryPostCode',
        'DeliveryCountry',
        'DeliveryState'
    );

    // Numeric. Fixed 4 characters.
    /**
     * @Assert\MinLength(4)
     * @Assert\MaxLength(4)
     * @Assert\Regex("{^\d\.\d\d$}")
     */
    protected $vpsProtocol;

    // Alphabetic. Max 15 characters.
    // "PAYMENT", "DEFERRED" or "AUTHENTICATE" ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"PAYMENT", "DEFERRED", "AUTHENTICATE"})
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
     * @Assert\Choice(callback = {"Insig\SagepayBundle\Util", "getCurrencyCodes"})
     */
    protected $currency;

    // Alphanumeric. Max 100 characters.
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(100)
     */
    protected $description;

    // Alphanumeric. Max 255 characters. RFC 1738
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(255)
     * @Assert\Url(protocols = {"http", "https"})
     */
    protected $notificationUrl;

    // Alphabetic. Max 20 characters.
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(20)
     * @Assert\Regex("{^[\pL /\\&\.\-']+$}")
     */
    protected $billingSurname;

    // Alphabetic. Max 20 characters.
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(20)
     * @Assert\Regex("{^[\pL /\\&\.\-']+$}")
     */
    protected $billingFirstnames;

    // Alphanumeric. Max 100 characters.
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(100)
     * @Assert\Regex("{^[\pL\d \+'/\\&:,\.\-\r\n\(\)]+$}")
     */
    protected $billingAddress1;

    // Optional. Alphanumeric. Max 100 characters.
    /**
     * @Assert\MaxLength(100)
     * @Assert\Regex("{^[\pL\d \+'/\\&:,\.\-\r\n\(\)]+$}")
     */
    protected $billingAddress2;

    // Alphanumeric. Max 40 characters.
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(40)
     * @Assert\Regex("{^[\pL\d \+'/\\&:,\.\-\r\n\(\)]+$}")
     */
    protected $billingCity;

    // Alphanumeric. Max 10 characters.
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(10)
     * @Assert\Regex("{^[a-zA-Z\d -]+$}")
     */
    protected $billingPostCode = '-';

    // Alphabetic. Max 2 characters. ISO 3166
    /**
     * @Assert\NotBlank()
     * @Assert\Country()
     */
    protected $billingCountry;

    // Optional (US customers only). Alphabetic. Max 2 characters.
    /**
     * @Assert\Choice(callback = {"Insig\SagepayBundle\Util", "getUsStateAbbreviations"})
     */
    protected $billingState;

    // Optional. Alphanumeric. Max 20 characters.
    /**
     * @Assert\MaxLength(20)
     * @Assert\Regex("{^[\d\-a-zA-Z\+ \(\)]+$}")
     */
    protected $billingPhone;

    // Alphabetic. Max 20 characters.
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(20)
     * @Assert\Regex("{^[\pL /\\&\.\-']+$}")
     */
    protected $deliverySurname;

    // Alphabetic. Max 20 characters.
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(20)
     * @Assert\Regex("{^[\pL /\\&\.\-']+$}")
     */
    protected $deliveryFirstnames;

    // Alphanumeric. Max 100 characters.
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(100)
     * @Assert\Regex("{^[\pL\d \+'/\\&:,\.\-\r\n\(\)]+$}")
     */
    protected $deliveryAddress1;

    // Optional. Alphanumeric. Max 100 characters.
    /**
     * @Assert\MaxLength(100)
     * @Assert\Regex("{^[\pL\d \+'/\\&:,\.\-\r\n\(\)]+$}")
     */
    protected $deliveryAddress2;

    // Alphanumeric. Max 40 characters.
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(40)
     * @Assert\Regex("{^[\pL\d \+'/\\&:,\.\-\r\n\(\)]+$}")
     */
    protected $deliveryCity;

    // Alphanumeric. Max 10 characters.
    /**
     * @Assert\NotBlank()
     * @Assert\MaxLength(10)
     * @Assert\Regex("{^[a-zA-Z\d -]+$}")
     */
    protected $deliveryPostCode = '-';

    // Alphabetic. Max 2 characters.
    /**
     * @Assert\NotBlank()
     * @Assert\Country()
     */
    protected $deliveryCountry;

    // Optional (US customers only). Alphabetic. Max 2 characters.
    /**
     * @Assert\Choice(callback = {"Insig\SagepayBundle\Util", "getUsStateAbbreviations"})
     */
    protected $deliveryState;

    // Optional. Alphanumeric. Max 20 characters.
    /**
     * @Assert\MaxLength(20)
     * @Assert\Regex("{^[\d\-a-zA-Z\+ \(\)]+$}")
     */
    protected $deliveryPhone;

    // Optional. Alphanumeric. Max 255 characters. RFC 532n
    /**
     * @Assert\MaxLength(255)
     * @Assert\Email()
     */
    protected $customerEmail;

    // Optional. Alphanumeric. Max 7500 characters.
    /**
     * @Assert\MaxLength(7500)
     */
    protected $basket;

    // Optional. Flag
    /**
     * @Assert\Min(0)
     * @Assert\Max(1)
     */
    protected $allowGiftAid;

    // Optional. Flag.
    /**
     * @Assert\Min(0)
     * @Assert\Max(3)
     */
    protected $applyAvsCv2;

    // Optional. Flag.
    /**
     * @Assert\Min(0)
     * @Assert\Max(3)
     */
    protected $apply3dSecure;

    // Optional. Alphabetic. Max 10 characters.
    /**
     * @Assert\Choice({"NORMAL", "LOW"})
     */
    protected $profile;

    // Optional. Flag.
    /**
     * @Assert\Min(0)
     * @Assert\Max(1)
     */
    protected $billingAgreement;

    // Optional. Alphanumeric. 1 character.
    /**
     * @Assert\Choice({"E", "C", "M"})
     */
    protected $accountType;

    // Validation ------------------------------------------------------------

    /**
     * Billing State is required if country is US
     *
     * @Assert\True
     */
    public function isBillingStateValid()
    {
        return !('US' === $this->billingCountry && !$this->billingState);
    }

    /**
     * Delivery State is required if country is US
     *
     * @Assert\True
     */
    public function isDeliveryStateValid()
    {
        return !('US' === $this->deliveryCountry && !$this->deliveryState);
    }

    /**
     * Checks that all the required fields are set
     *
     * @Assert\True
     */
    public function isComplete()
    {
        return 0 === count(array_diff(self::$required, array_keys($this->toArray())));
    }

    // public API ------------------------------------------------------------

    public function __construct()
    {
        $this->setVendorTxCode(md5(uniqid(rand(), true)));
    }

    public function getVpsProtocol()
    {
        return $this->vpsProtocol;
    }

    public function getTxType()
    {
        return $this->txType;
    }

    public function getVendor()
    {
        return $this->vendor;
    }

    public function getVendorTxCode()
    {
        return $this->vendorTxCode;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getNotificationURL()
    {
        return $this->notificationUrl;
    }

    public function getBillingSurname()
    {
        return $this->billingSurname;
    }

    public function getBillingFirstnames()
    {
        return $this->billingFirstnames;
    }

    public function getBillingAddress1()
    {
        return $this->billingAddress1;
    }

    public function getBillingAddress2()
    {
        return $this->billingAddress2;
    }

    public function getBillingCity()
    {
        return $this->billingCity;
    }

    public function getBillingPostCode()
    {
        return $this->billingPostCode;
    }

    public function getBillingCountry()
    {
        return $this->billingCountry;
    }

    public function getBillingState()
    {
        return $this->billingState;
    }

    public function getBillingPhone()
    {
        return $this->billingPhone;
    }

    public function getDeliverySurname()
    {
        return $this->deliverySurname;
    }

    public function getDeliveryFirstnames()
    {
        return $this->deliveryFirstnames;
    }

    public function getDeliveryAddress1()
    {
        return $this->deliveryAddress1;
    }

    public function getDeliveryAddress2()
    {
        return $this->deliveryAddress2;
    }

    public function getDeliveryCity()
    {
        return $this->deliveryCity;
    }

    public function getDeliveryPostCode()
    {
        return $this->deliveryPostCode;
    }

    public function getDeliveryCountry()
    {
        return $this->deliveryCountry;
    }

    public function getDeliveryState()
    {
        return $this->deliveryState;
    }

    public function getDeliveryPhone()
    {
        return $this->deliveryPhone;
    }

    public function getCustomerEmail()
    {
        return $this->customerEmail;
    }

    public function getBasket()
    {
        return $this->basket;
    }

    public function getAllowGiftAid()
    {
        return $this->allowGiftAid;
    }

    public function getApplyAvsCv2()
    {
        return $this->applyAvsCv2;
    }

    public function getApply3dSecure()
    {
        return $this->apply3dSecure;
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function getBillingAgreement()
    {
        return $this->billingAgreement;
    }

    public function getAccountType()
    {
        return $this->accountType;
    }

    public function setVpsProtocol($value)
    {
        $this->vpsProtocol = number_format($value, 2);
    }

    public function setTxType($value)
    {
        throw new IllegalOperationException('Instantiate the correct transaction request subclass instead.');
    }

    public function setVendor($value)
    {
        $this->vendor = $value;
    }

    public function setVendorTxCode($value)
    {
        $this->vendorTxCode = $value;
    }

    public function setAmount($value)
    {
        $this->amount = (float) $value;
    }

    public function setCurrency($value)
    {
        $this->currency = $value;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function setNotificationURL($value)
    {
        $this->notificationUrl = $value;
    }

    public function setBillingSurname($value)
    {
        $this->billingSurname = $value;
    }

    public function setBillingFirstnames($value)
    {
        $this->billingFirstnames = $value;
    }

    public function setBillingAddress1($value)
    {
        $this->billingAddress1 = $value;
    }

    public function setBillingAddress2($value)
    {
        $this->billingAddress2 = $value;
    }

    public function setBillingCity($value)
    {
        $this->billingCity = $value;
    }

    public function setBillingPostCode($value)
    {
        $this->billingPostCode = $value ? $value : '-';
    }

    public function setBillingCountry($value)
    {
        $this->billingCountry = $value;
    }

    public function setBillingState($value)
    {
        $this->billingState = $value;
    }

    public function setBillingPhone($value)
    {
        $this->billingPhone = $value;
    }

    public function setDeliverySurname($value)
    {
        $this->deliverySurname = $value;
    }

    public function setDeliveryFirstnames($value)
    {
        $this->deliveryFirstnames = $value;
    }

    public function setDeliveryAddress1($value)
    {
        $this->deliveryAddress1 = $value;
    }

    public function setDeliveryAddress2($value)
    {
        $this->deliveryAddress2 = $value;
    }

    public function setDeliveryCity($value)
    {
        $this->deliveryCity = $value;
    }

    public function setDeliveryPostCode($value)
    {
        $this->deliveryPostCode = $value ? $value : '-';
    }

    public function setDeliveryCountry($value)
    {
        $this->deliveryCountry = $value;
    }

    public function setDeliveryState($value)
    {
        $this->deliveryState = $value;
    }

    public function setDeliveryPhone($value)
    {
        $this->deliveryPhone = $value;
    }

    public function setCustomerEmail($value)
    {
        $this->customerEmail = $value;
    }

    public function setBasket($value)
    {
        $this->basket = $value;
    }

    public function setAllowGiftAid($value)
    {
        $this->allowGiftAid = (int) $value;
    }

    public function setApplyAvsCv2($value)
    {
        $this->applyAvsCv2 = (int) $value;
    }

    public function setApply3dSecure($value)
    {
        $this->apply3dSecure = (int) $value;
    }

    public function setProfile($value)
    {
        $this->profile = $value;
    }

    public function setBillingAgreement($value)
    {
        $this->billingAgreement = (int) $value;
    }

    public function setAccountType($value)
    {
        $this->accountType = $value;
    }

    // convenience setters ---------------------------------------------------

    /**
     * Accepts an associative array with:
     * firstname, surname, address_1, address_2, city, postcode, country,
     * state and phone
     */
    public function setBillingAddress(array $address)
    {
        $this->setBillingFirstnames ($address['firstname']);
        $this->setBillingSurname    ($address['surname']);
        $this->setBillingAddress1   ($address['address_1']);
        if (isset($address['address_2']) && !empty($address['address_2'])) {
            $this->setBillingAddress2   ($address['address_2']);
        }
        $this->setBillingCity       ($address['city']);
        $this->setBillingPostcode   ($address['postcode']);
        $this->setBillingCountry    ($address['country']);
        if ('US' === $address['country'] && isset($address['state'])) {
            $this->setBillingState    ($address['state']);
        }
        $this->setBillingPhone      ($address['phone']);
    }

    /**
     * Accepts an associative array with:
     * firstname, surname, address_1, address_2, city, postcode, country,
     * state and phone
     */
    public function setDeliveryAddress(array $address)
    {
        $this->setDeliveryFirstnames  ($address['firstname']);
        $this->setDeliverySurname     ($address['surname']);
        $this->setDeliveryAddress1    ($address['address_1']);
        if (isset($address['address_2']) && !empty($address['address_2'])) {
            $this->setDeliveryAddress2    ($address['address_2']);
        }
        $this->setDeliveryCity        ($address['city']);
        $this->setDeliveryPostcode    ($address['postcode']);
        $this->setDeliveryCountry     ($address['country']);
        if ('US' === $address['country'] && isset($address['state'])) {
            $this->setDeliveryState     ($address['state']);
        }
        $this->setDeliveryPhone       ($address['phone']);
    }

    /**
     * Accepts an array of items
     * Each item is an associative array with:
     * name, quantity, price and tax
     */
    public function createBasketFromArray(array $items)
    {
        $lines = array();
        foreach ($items as $item) {
            $lines[] = sprintf(
                '%s:%d:%.2f:%.2f:%.2f:%.2f',
                str_replace(':', ' -', $item['name']), // convert any colons in the name
                $item['quantity'],
                $item['price'],
                $item['tax'],
                $item['price'] + $item['tax'],
                $item['quantity'] * ($item['price'] + $item['tax'])
            );
        }
        $this->setBasket(count($lines) . ':' . implode(':', $lines));
    }

    // output ----------------------------------------------------------------

    public function toArray()
    {
        /**
         * Returns an associative array of properties
         * Keys are in the correct Sagepay naming format
         * Any values which could contain accented characters are converted
         * from UTF-8 to ISO-8859-1
         * Empty keys are removed
         */
        return array_filter(
            array(
                'VPSProtocol'           => $this->vpsProtocol,
                'TxType'                => $this->txType,
                'Vendor'                => $this->vendor,
                'VendorTxCode'          => $this->vendorTxCode,
                'Amount'                => number_format($this->amount, 2),
                'Currency'              => $this->currency,
                'Description'           => $this->description,
                'NotificationURL'       => $this->notificationUrl,
                'BillingSurname'        => utf8_decode($this->billingSurname),
                'BillingFirstnames'     => utf8_decode($this->billingFirstnames),
                'BillingAddress1'       => utf8_decode($this->billingAddress1),
                'BillingAddress2'       => utf8_decode($this->billingAddress2),
                'BillingCity'           => utf8_decode($this->billingCity),
                'BillingPostCode'       => $this->billingPostCode,
                'BillingCountry'        => $this->billingCountry,
                'BillingState'          => $this->billingState,
                'BillingPhone'          => $this->billingPhone,
                'DeliverySurname'       => utf8_decode($this->deliverySurname),
                'DeliveryFirstnames'    => utf8_decode($this->deliveryFirstnames),
                'DeliveryAddress1'      => utf8_decode($this->deliveryAddress1),
                'DeliveryAddress2'      => utf8_decode($this->deliveryAddress2),
                'DeliveryCity'          => utf8_decode($this->deliveryCity),
                'DeliveryPostCode'      => $this->deliveryPostCode,
                'DeliveryCountry'       => $this->deliveryCountry,
                'DeliveryState'         => $this->deliveryState,
                'DeliveryPhone'         => $this->deliveryPhone,
                'CustomerEMail'         => $this->customerEmail,
                'Basket'                => $this->basket,
                'AllowGiftAid'          => $this->allowGiftAid,
                'ApplyAVSCV2'           => $this->applyAvsCv2,
                'Apply3DSecure'         => $this->apply3dSecure,
                'Profile'               => $this->profile,
                'BillingAgreement'      => $this->billingAgreement,
                'AccountType'           => $this->accountType
            )
        );
    }

    public function getQueryString()
    {
        return http_build_query($this->toArray());
    }
}