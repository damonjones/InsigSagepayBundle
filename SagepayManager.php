<?php

namespace Insig\SagepayBundle;

use Symfony\Component\Validator\Validator;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

use Insig\SagepayBundle\Exception\InvalidRequestException;
use Insig\SagepayBundle\Exception\InvalidNotificationException;
use Insig\SagepayBundle\Exception\InvalidConfigurationException;

use Buzz\Message\Request as BuzzRequest;
use Buzz\Message\Response as BuzzResponse;
use Buzz\Client\ClientInterface;

use Insig\SagepayBundle\Model\Base\RegistrationRequest as BaseRegistrationRequest;
use Insig\SagepayBundle\Model\Base\NotificationRequest as BaseNotificationRequest;
use Insig\SagepayBundle\Model\Base\NotificationResponse as BaseNotificationResponse;

use Insig\SagepayBundle\Model\Transaction\Registration\Request as TransactionRegistrationRequest;
use Insig\SagepayBundle\Model\Transaction\Registration\Response as TransactionRegistrationResponse;

use Insig\SagepayBundle\Model\Transaction\Notification\Request as TransactionNotificationRequest;
use Insig\SagepayBundle\Model\Transaction\Notification\Response as TransactionNotificationResponse;

use Insig\SagepayBundle\Model\Token\Registration\Request as TokenRegistrationRequest;
use Insig\SagepayBundle\Model\Token\Registration\Response as TokenRegistrationResponse;

use Insig\SagepayBundle\Model\Token\Notification\Request as TokenNotificationRequest;
use Insig\SagepayBundle\Model\Token\Notification\Response as TokenNotificationResponse;

use Insig\SagepayBundle\Model\Transaction\TransactionInterface;

use Insig\SagepayBundle\Model\Additional\ReleaseRequest;
use Insig\SagepayBundle\Model\Additional\ReleaseResponse;
use Insig\SagepayBundle\Model\Additional\AbortRequest;
use Insig\SagepayBundle\Model\Additional\AbortResponse;
use Insig\SagepayBundle\Model\Additional\RefundRequest;
use Insig\SagepayBundle\Model\Additional\RefundResponse;
use Insig\SagepayBundle\Model\Additional\RepeatRequest;
use Insig\SagepayBundle\Model\Additional\RepeatResponse;
use Insig\SagepayBundle\Model\Additional\RepeatDeferredRequest;
use Insig\SagepayBundle\Model\Additional\RepeatDeferredResponse;
use Insig\SagepayBundle\Model\Additional\VoidRequest;
use Insig\SagepayBundle\Model\Additional\VoidResponse;
use Insig\SagepayBundle\Model\Additional\CancelRequest;
use Insig\SagepayBundle\Model\Additional\CancelResponse;
use Insig\SagepayBundle\Model\Additional\AuthoriseRequest;
use Insig\SagepayBundle\Model\Additional\AuthoriseResponse;

class SagepayManager
{
    protected $mode;
    protected $vpsProtocol;
    protected $vendor;

    // Symfony services
    protected $validator;
    protected $client;

    // Site callback URLs/route names
    protected $redirectUrls;

    // public API ------------------------------------------------------------
    public function __construct($vendor, $vpsProtocol, $mode)
    {
        if (!in_array($mode, array('simulator', 'test', 'live'))) {
            throw new InvalidConfigurationException('SagePay mode must be one of "simulator", "test" or "live".');
        }

        $this->vendor       = $vendor;
        $this->vpsProtocol  = $vpsProtocol;
        $this->mode         = $mode;
    }

    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Set the callback URLs for each Sagepay outcome
     * If URLs are not fully-qualified, they are assumed to be route names
     * so the Symfony routing service will convert them to absolute URLs
     */
    public function setRedirectUrls(array $redirectUrls, Router $router)
    {
        $this->redirectUrls = array_map(
            function($url) use ($router) {
                return 'http' !== substr($url, 0, 4) ? $router->generate($url, array(), true) : $url;
            },
            $redirectUrls
        );
    }

    // Transaction

    /**
     * Register Transaction
     *
     * Sends an http post representation of a Transaction Request (Payment|Authenticate|Deferred) to Sagepay
     * Returns a TransactionRegistrationResponse object populated from the server's response
     *
     * @param \Insig\SagepayBundle\Model\Transaction\Registration\Request $request
     * @return \Insig\SagepayBundle\Model\Transaction\Registration\Response $response
     * @author Damon Jones
     */
    public function registerTransaction(TransactionRegistrationRequest $request, TransactionInterface &$transaction)
    {
        $responseArray = $this->sendRequest($request);
        $response = new TransactionRegistrationResponse($responseArray);

        $transaction->setVendorTxCode($request->getVendorTxCode());
        $transaction->setVpsTxId($response->getVpsTxId());
        $transaction->setSecurityKey($response->getSecurityKey());
        $transaction->setAmount($request->getAmount());
        $transaction->setTxType($request->getTxType());

        return $response;
    }

    /**
     * Create Transaction Notification
     *
     * @param string $data
     * @throws \Insig\SagepayBundle\Exception\InvalidNotificationException
     * @return \Insig\SagepayBundle\Model\Transaction\Notification\Request
     * @author Damon Jones
     */
    public function createTransactionNotification($data)
    {
        parse_str($data, $arr);
        $notification = new TransactionNotificationRequest($arr);

        // validate the notification if validation is enabled
        if ($this->validator) {
            $errors = $this->validator->validate($notification);
            if (count($errors)) {
                throw new InvalidNotificationException;
            }
        }

        return $notification;
    }

    /**
     * Create Transaction Notification Response
     *
     * @param string $status
     * @param string $statusDetail
     * @return \Insig\SagepayBundle\Model\Transaction\Notification\Response
     * @author Damon Jones
     */
    public function createTransactionNotificationResponse($status, $statusDetail = null)
    {
        /**
         * You should send OK in all circumstances where no errors occur
         * in validating the Notification POST, so even if Sage Pay send
         * you a status of ABORT or NOTAUTHED in A3 above, you should
         * reply with an OK and a RedirectURL that points to a page
         * informing the customer that the transaction did not complete.
         */
        return new TransactionNotificationResponse(
            in_array($status, array('INVALID', 'ERROR')) ? $status : 'OK',
            $this->redirectUrls[strtolower($status)],
            $statusDetail
        );
    }

    // Token

    /**
     * Register Token
     *
     * Sends an http post representation of a Token Registration Request to Sagepay
     * Returns a TokenRegistrationResponse object populated from the server's response
     *
     * @param \Insig\SagepayBundle\Model\Token\Registration\Request $request
     * @return \Insig\SagepayBundle\Model\Token\Registration\Response $response
     * @author Damon Jones
     */
    public function registerToken(TokenRegistrationRequest $request)
    {
        $responseArray = $this->sendRequest($request);

        return new TokenRegistrationResponse($responseArray);
    }

    /**
     * Create Token Notification
     *
     * @param string $data
     * @throws \Insig\SagepayBundle\Exception\InvalidNotificationException
     * @return \Insig\SagepayBundle\Model\Token\Notification\Request
     * @author Damon Jones
     */
    public function createTokenNotification($data)
    {
        parse_str($data, $arr);
        $notification = new TokenNotificationRequest($arr);

        // validate the notification if validation is enabled
        if ($this->validator) {
            $errors = $this->validator->validate($notification);
            if (count($errors)) {
                throw new InvalidNotificationException;
            }
        }

        return $notification;
    }

    /**
     * Create Token Notification Response
     *
     * @param string $status
     * @param string $statusDetail
     * @return \Insig\SagepayBundle\Model\Token\Notification\Response
     * @author Damon Jones
     */
    public function createTokenNotificationResponse($status, $statusDetail = null)
    {
        /**
        * You should send OK in all circumstances where no errors occur
        * in validating the Notification POST
        */
        return new TokenNotificationResponse(
            in_array($status, array('MALFORMED', 'INVALID')) ? $status : 'OK',
            $this->redirectUrls[strtolower($status)],
            $statusDetail
        );
    }

    // Additional Transaction Protocols

    public function performRelease(TransactionInterface $transaction, $amount = null)
    {
        $request = new ReleaseRequest($transaction);
        if ($amount) {
            $request->setReleaseAmount($amount);
        }
        $responseArray = $this->sendRequest($request);

        return new ReleaseResponse($responseArray);
    }

    public function performAbort(TransactionInterface $transaction)
    {
        $request = new AbortRequest($transaction);
        $responseArray = $this->sendRequest($request);

        return new AbortResponse($responseArray);
    }

    public function performRefund(TransactionInterface $transaction, $amount, $currency, $description, TransactionInterface &$relatedTransaction)
    {
        $request = new RefundRequest($transaction, $amount, $currency, $description);
        $responseArray = $this->sendRequest($request);
        $response = new RefundResponse($responseArray);

        $relatedTransaction->setVendorTxCode($request->getVendorTxCode());
        $relatedTransaction->setVpsTxId($response->getVpsTxId());
        $relatedTransaction->setTxAuthNo($response->getTxAuthNo());
        $relatedTransaction->setTxType($request->getTxType());
        $relatedTransaction->setAmount($request->getAmount());

        return $response;
    }

    public function performRepeat(TransactionInterface $transaction, $amount, $currency, $description, $cv2, TransactionInterface &$relatedTransaction)
    {
        $request = new RepeatRequest($transaction, $amount, $currency, $description, $cv2);
        $responseArray = $this->sendRequest($request);
        $response = new RepeatResponse($responseArray);

        $relatedTransaction->setVendorTxCode($request->getVendorTxCode());
        $relatedTransaction->setVpsTxId($response->getVpsTxId());
        $relatedTransaction->setTxAuthNo($response->getTxAuthNo());
        $relatedTransaction->setSecurityKey($response->getSecurityKey());
        $relatedTransaction->setTxType($request->getTxType());
        $relatedTransaction->setAmount($request->getAmount());

        return $response;
    }

    public function performRepeatDeferred(TransactionInterface $transaction, $amount, $currency, $description, $cv2, TransactionInterface &$relatedTransaction)
    {
        $request = new RepeatDeferredRequest($transaction, $amount, $currency, $description, $cv2);
        $responseArray = $this->sendRequest($request);
        $response = new RepeatResponse($responseArray);

        $relatedTransaction->setVendorTxCode($request->getVendorTxCode());
        $relatedTransaction->setVpsTxId($response->getVpsTxId());
        $relatedTransaction->setTxAuthNo($response->getTxAuthNo());
        $relatedTransaction->setSecurityKey($response->getSecurityKey());
        $relatedTransaction->setTxType($request->getTxType());
        $relatedTransaction->setAmount($request->getAmount());

        return $response;
    }

    public function performVoid(TransactionInterface $transaction)
    {
        $request = new VoidRequest($transaction);
        $response = $this->sendRequest($request);

        return new VoidResponse($response);
    }

    public function performCancel(TransactionInterface $transaction)
    {
        $request = new CancelRequest($transaction);
        $response = $this->sendRequest($request);

        return new CancelResponse($response);
    }

    public function performAuthorise(TransactionInterface $transaction, $amount, $description, $applyAvsCv2, TransactionInterface &$relatedTransaction)
    {
        $request = new AuthoriseRequest($transaction, $amount, $description, $applyAvsCv2);
        $responseArray = $this->sendRequest($request);
        $response = new AuthoriseResponse($responseArray);

        $relatedTransaction->setVendorTxCode($request->getVendorTxCode());
        $relatedTransaction->setVpsTxId($response->getVpsTxId());
        $relatedTransaction->setTxAuthNo($response->getTxAuthNo());
        $relatedTransaction->setSecurityKey($response->getSecurityKey());
        $relatedTransaction->setTxType($request->getTxType());
        $relatedTransaction->setAmount($request->getAmount());

        return $response;
    }

    /**
     * Is Notification Authentic
     *
     * @param \Insig\SagepayBundle\Model\NotificationRequest $notification
     * @param string $securityKey
     * @return boolean
     * @author Damon Jones
     */
    public function isNotificationAuthentic(BaseNotificationRequest $request, $securityKey)
    {
        return
            $request->getVpsSignature()
            ===
            $request->getComputedSignature($this->vendor, $securityKey);
    }

    /**
     * Format Notification Response
     *
     * Returns a string representation of the notification response
     * in the format required to send to Sagepay (name=value pairs separated by "\r\n")
     * @param \Insig\SagepayBundle\Model\NotificationResponse $response
     * @return string
     * @author Damon Jones
     */
    public function formatNotificationResponse(BaseNotificationResponse $response)
    {
        return urldecode(http_build_query($response->toArray(), null, "\r\n"));
    }

    // Protected/Private methods

    /**
     * Send Request
     *
     * Sends a cURL POST of the request properties
     * Returns the server's response string
     *
     * @param \Insig\SagepayBundle\Model\RegistrationRequest $request
     * @throws \Insig\SagepayBundle\Exception\InvalidRequestException
     * @return array
     * @author Damon Jones
     */
    protected function sendRequest(BaseRegistrationRequest $request)
    {
        $request->setVpsProtocol($this->vpsProtocol);
        $request->setVendor($this->vendor);

        // validate the request if validation is enabled
        if ($this->validator) {
            $errors = $this->validator->validate($request);
            if (count($errors)) {
                var_dump($errors);
                throw new InvalidRequestException;
            }
        }

        $service = strtolower($request->getTxType());

        if (in_array($service, array('payment', 'authenticate', 'deferred'))) {
            $service = 'simulator' === $this->mode ? 'Register' : 'vspserver-register';
        }

        if ('repeatdeferred' === $service) {
            $service = 'repeat';
        }

        switch ($this->mode) {
            case 'simulator':
                $host = 'https://test.sagepay.com';
                $resource = sprintf('/Simulator/VSPServerGateway.asp?Service=Vendor%sTx', $service);
                break;
            case 'test':
                $host = 'https://test.sagepay.com';
                $resource = sprintf('/gateway/service/%s.vsp', $service);
                break;
            case 'live':
                $host = 'https://live.sagepay.com';
                $resource = sprintf('/gateway/service/%s.vsp', $service);
                break;
        }

        $buzzRequest = new BuzzRequest('POST', $resource, $host);
        $buzzRequest->setContent(http_build_query($request->toArray()));
        $buzzResponse = new BuzzResponse();

        $this->client->send($buzzRequest, $buzzResponse);

        $responseString = $buzzResponse->getContent();

        /** Sagepay returns a response string consisting of key/value pairs
         * Each key value pair is separated by an equals sign (key=value)
         * Pairs are separated by CRLF
         */
        parse_str(str_replace("\r\n", '&', $responseString), $responseArray);

        return $responseArray;
    }
}
