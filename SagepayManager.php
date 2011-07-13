<?php

namespace Insig\SagepayBundle;

use Insig\SagepayBundle\TransactionRegistration\Request;
use Insig\SagepayBundle\TransactionRegistration\Response;
use Insig\SagepayBundle\Notification\Notification;
use Insig\SagepayBundle\Notification\Response as NotificationResponse;

use Insig\SagepayBundle\Exception\InvalidRequestException;
use Insig\SagepayBundle\Exception\InvalidNotificationException;
use Insig\SagepayBundle\Exception\CurlException;

class SagepayManager
{
    protected $validator;
    protected $router;

    protected $sagepayUrl;
    protected $vpsProtocol;
    protected $vendor;
    protected $notificationUrl;
    protected $redirectUrls;

    /**
     * isRoute
     *
     * @param string $url
     * @return boolean
     * @author Damon Jones
     */
    protected function isRoute($url)
    {
        return '@' === $url[0];
    }

    /**
     * convertRouteToAbsoluteUrl
     *
     * @param string $route
     * @param string $parameters
     * @return string
     * @author Damon Jones
     */
    protected function convertRouteToAbsoluteUrl($route, $parameters = array())
    {
        return $this->router->generate(substr($route, 1), $parameters, true);
    }

    // public API ------------------------------------------------------------
    public function setValidator($validator)
    {
        $this->validator = $validator;
    }

    public function setRouter($router)
    {
        $this->router = $router;
    }

    public function setSagepayUrl($sagepayUrl)
    {
        $this->sagepayUrl = $sagepayUrl;
    }

    public function setVpsProtocol($vpsProtocol)
    {
        $this->vpsProtocol = $vpsProtocol;
    }

    public function setVendor($vendor)
    {
        $this->vendor = $vendor;
    }

    public function setNotificationUrl($notificationUrl)
    {
        $this->notificationUrl = $notificationUrl;
    }

    public function setRedirectUrls(array $redirectUrls)
    {
        $this->redirectUrls = $redirectUrls;
    }

    /**
     * sendTransactionRegistrationRequest
     *
     * Sends a cURL POST of the request properties as an http_query
     * Returns a Response object populated from the server's response
     *
     * @param \Insig\SagepayBundle\TransactionRegistration\Request $request
     * @throws \Insig\SagepayBundle\InvalidRequestException
     * @throws \Insig\SagepayBundle\CurlException
     * @return \Insig\SagepayBundle\TransactionRegistration\Response $response
     * @author Damon Jones
     */
    public function sendTransactionRegistrationRequest(Request $request)
    {
        $request->setVpsProtocol($this->vpsProtocol);
        $request->setVendor($this->vendor);

        if ($this->isRoute($this->notificationUrl)) {
            $request->setNotificationUrl(
                $this->convertRouteToAbsoluteUrl($this->notificationUrl)
            );
        } else {
            $request->setNotificationUrl($this->notificationUrl);
        }

        $errors = $this->validator->validate($request);
        if (count($errors)) {
            throw new InvalidRequestException;
        }

        $curlSession = curl_init();
        curl_setopt_array(
            $curlSession,
            array(
                CURLOPT_URL             =>  $this->url,
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

        return new Response($response);
    }

    /**
     * createNotification
     *
     * @param string $string
     * @throws \Insig\SagepayBundle\Exception\InvalidNotificationException
     * @return \Insig\SagepayBundle\Notification\Notification
     * @author Damon Jones
     */
    public function createNotification($string)
    {
        $notification = new Notification($string);
        // validate the notification
        $errors = $this->validator->validate($notification);
        if (count($errors)) {
            throw new InvalidNotificationException;
        }

        return $notification;
    }

    /**
     * isNotificationAuthentic
     *
     * @param \Insig\SagepayBundle\Notification\Notification $notification
     * @param string $securityKey
     * @return boolean
     * @author Damon Jones
     */
    public function isNotificationAuthentic(Notification $notification, $securityKey)
    {
        $computedSignature = strtoupper(
            md5(
                $notification->getVpsTxId() .
                $notification->getVendorTxCode() .
                $notification->getStatus() .
                $notification->getTxAuthNo() .
                $this->vendor .
                $notification->getAvsCv2() .
                $securityKey .
                $notification->getAddressResult() .
                $notification->getPostCodeResult() .
                $notification->getCv2Result() .
                $notification->getGiftAid() .
                $notification->get3dSecureStatus() .
                $notification->getCavv() .
                $notification->getAddressStatus() .
                $notification->getPayerStatus() .
                $notification->getCardType() .
                $notification->getLast4Digits()
            )
        );

        return $notification->getVpsSignature() === $computedSignature;
    }

    /**
     * createNotificationResponse
     *
     * @param string $status
     * @param string $statusDetail
     * @return \Insig\SagepayBundle\Notification\Response
     * @author Damon Jones
     */
    public function createNotificationResponse($status, $statusDetail = null)
    {
        $redirectUrl = $this->redirectUrls[strtolower($status)];
        if ($this->isRoute($redirectUrl)) {
            $redirectUrl = $this->convertRouteToAbsoluteUrl($redirectUrl);
        }

        return new NotificationResponse($status, $redirectUrl, $statusDetail);
    }
}