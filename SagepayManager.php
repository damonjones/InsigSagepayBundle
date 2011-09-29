<?php

namespace Insig\SagepayBundle;

use Insig\SagepayBundle\Exception\CurlException;
use Insig\SagepayBundle\Exception\InvalidRequestException;
use Insig\SagepayBundle\Exception\InvalidNotificationException;

use Insig\SagepayBundle\Model\RegistrationRequest;
use Insig\SagepayBundle\Model\NotificationRequest;

use Insig\SagepayBundle\Model\Transaction\Registration\Request as TransactionRegistrationRequest;
use Insig\SagepayBundle\Model\Transaction\Registration\Response as TransactionRegistrationResponse;

use Insig\SagepayBundle\Model\Transaction\Notification\Request as TransactionNotificationRequest;
use Insig\SagepayBundle\Model\Transaction\Notification\Response as TransactionNotificationResponse;

use Insig\SagepayBundle\Model\Token\Registration\Request as TokenRegistrationRequest;
use Insig\SagepayBundle\Model\Token\Registration\Response as TokenRegistrationResponse;

use Insig\SagepayBundle\Model\Token\Notification\Request as TokenNotificationRequest;
use Insig\SagepayBundle\Model\Token\Notification\Response as TokenNotificationResponse;

class SagepayManager
{
    protected $validator;
    protected $router;

    // Sagepay URLs
    protected $gatewayUrl;
    protected $registerTokenUrl;
    protected $removeTokenUrl;

    protected $vpsProtocol;
    protected $vendor;

    // Site URLs/route names
    protected $transactionNotificationUrl;
    protected $tokenNotificationUrl;
    protected $redirectUrls;

    // public API ------------------------------------------------------------
    public function setValidator($validator)
    {
        $this->validator = $validator;
    }

    public function setRouter($router)
    {
        $this->router = $router;
    }

    public function setGatewayUrl($url)
    {
        $this->gatewayUrl = $url;
    }

    public function setRegisterTokenUrl($url)
    {
        $this->registerTokenUrl = $url;
    }

    public function setRemoveTokenUrl($url)
    {
        $this->removeTokenUrl = $url;
    }

    public function setVpsProtocol($vpsProtocol)
    {
        $this->vpsProtocol = $vpsProtocol;
    }

    public function setVendor($vendor)
    {
        $this->vendor = $vendor;
    }

    public function getVendor()
    {
        return $this->vendor;
    }

    public function setTransactionNotificationUrl($url)
    {
        $this->transactionNotificationUrl = $url;
    }

    public function setTokenNotificationUrl($url)
    {
        $this->tokenNotificationUrl = $url;
    }

    public function setRedirectUrls(array $redirectUrls)
    {
        $this->redirectUrls = $redirectUrls;
    }

    // Transaction

    /**
     * Send Transaction Registration Request
     *
     * Sends a cURL POST of the request properties as an http_query
     * Returns a Response object populated from the server's response
     *
     * @param \Insig\SagepayBundle\Model\Transaction\Registration\Request $request
     * @return \Insig\SagepayBundle\Model\Transaction\Registration\Response $response
     * @author Damon Jones
     */
    public function sendTransactionRegistrationRequest(TransactionRegistrationRequest $request)
    {
        $response = $this->sendRegistrationRequest(
            $request,
            $this->gatewayUrl,
            $this->transactionNotificationUrl
        );

        return new TransactionRegistrationResponse($response);
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
        $notification = new TransactionNotificationRequest($data);
        // validate the notification
        $errors = $this->validator->validate($notification);
        if (count($errors)) {
            throw new InvalidNotificationException;
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
            $this->convertRouteToAbsoluteUrl($this->redirectUrls[strtolower($status)]),
            $statusDetail
        );
    }

    // Token

    /**
     * Send Token Registration Request
     *
     * Sends a cURL POST of the request properties as an http_query
     * Returns a Response object populated from the server's response
     *
     * @param \Insig\SagepayBundle\Model\Token\Registration\Request $request
     * @return \Insig\SagepayBundle\Model\Token\Registration\Response $response
     * @author Damon Jones
     */
    public function sendTokenRegistrationRequest(TokenRegistrationRequest $request)
    {
        $response = $this->sendRegistrationRequest(
            $request,
            $this->registerTokenUrl,
            $this->tokenNotificationUrl
        );

        return new TokenRegistrationResponse($response);
    }

    /**
     * Create Token Notification
     *
     * @param string $string
     * @throws \Insig\SagepayBundle\Exception\InvalidNotificationException
     * @return \Insig\SagepayBundle\Model\Token\Notification\Request
     * @author Damon Jones
     */
    public function createTokenNotification($string)
    {
        $notification = new TokenNotificationRequest($string);
        // validate the notification
        $errors = $this->validator->validate($notification);
        if (count($errors)) {
            throw new InvalidNotificationException;
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
            $this->convertRouteToAbsoluteUrl(
                'OK' === $status ? $this->redirectUrls['token_ok'] : $this->redirectUrls['token_error']
            ),
            $statusDetail
        );
    }

    // Common

    /**
     * Is Notification Authentic
     *
     * @param \Insig\SagepayBundle\Model\NotificationRequest $notification
     * @param string $securityKey
     * @return boolean
     * @author Damon Jones
     */
    public function isNotificationAuthentic(NotificationRequest $request, $securityKey)
    {
        return
            $request->getVpsSignature()
            ===
            $request->getComputedSignature($this->vendor, $securityKey);
    }

    // protected methods

    /**
     * Converts a route name to an absolute URL
     *
     * @param string $route Follows the symfony1 convention of an '@' prefix
     * @param string $parameters URL parameters array
     * @return string
     * @author Damon Jones
     */
    protected function convertRouteToAbsoluteUrl($route, $parameters = array())
    {
        if ('@' === $route[0]) {
            return $this->router->generate(substr($route, 1), $parameters, true);
        } else {
            return $route;
        }
    }

    /**
     * Send Registration Request
     *
     * Sends a cURL POST of the request properties as an http_query
     * Returns a Response object populated from the server's response
     *
     * @param \Insig\SagepayBundle\Model\RegistrationRequest $request
     * @throws \Insig\SagepayBundle\Exception\InvalidRequestException
     * @throws \Insig\SagepayBundle\Exception\CurlException
     * @return string $response
     * @author Damon Jones
     */
    protected function sendRegistrationRequest(RegistrationRequest $request, $registrationUrl, $notificationUrl)
    {
        $request->setVpsProtocol($this->vpsProtocol);
        $request->setVendor($this->vendor);
        $request->setNotificationUrl(
            $this->convertRouteToAbsoluteUrl($notificationUrl)
        );

        $errors = $this->validator->validate($request);
        if (count($errors)) {
            throw new InvalidRequestException;
        }

        $curlSession = curl_init();
        curl_setopt_array(
            $curlSession,
            array(
                CURLOPT_URL             =>  $registrationUrl,
                CURLOPT_HEADER          =>  false,
                CURLOPT_POST            =>  true,
                CURLOPT_POSTFIELDS      =>  $request->getQueryString(),
                CURLOPT_RETURNTRANSFER  =>  true,
                CURLOPT_TIMEOUT         =>  30,
                CURLOPT_SSL_VERIFYPEER  =>  false,
                CURLOPT_SSL_VERIFYHOST  =>  true
            )
        );
        $response = curl_exec($curlSession);
        $error = curl_error($curlSession);
        curl_close($curlSession);

        if (false === $response) {
            throw new CurlException($error);
        }

        return $response;
    }
}