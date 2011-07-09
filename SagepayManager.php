<?php

namespace Insig\SagepayBundle;

use Insig\SagepayBundle\TransactionRegistration\Request;
use Insig\SagepayBundle\TransactionRegistration\Response;
use Insig\SagepayBundle\Notification\Notification;
use Insig\SagepayBundle\Notification\Response as NotificationResponse;

class SagepayManager
{
    protected $validator;
    protected $router;

    protected $sagepayUrl;
    protected $vpsProtocol;
    protected $vendor;
    protected $notificationUrl;
    protected $redirectUrls;

    protected function isRoute($url)
    {
        return '@' === $url[0];
    }

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

    /**
     * If the notification URL starts with an "@" then it will be treated as a
     * route name and we will generate the absolute URL from the route name
     * when needed (with optional parameters)
     */
    public function setNotificationUrl($notificationUrl)
    {
        $this->notificationUrl = $notificationUrl;
    }

    /**
     * If the redirect URL starts with an "@" then it will be treated as a
     * route name and we will generate the absolute URL from the route name
     * when needed (with optional parameters)
     */
    public function setRedirectUrls(array $redirectUrls)
    {
        $this->redirectUrls = $redirectUrls;
    }

    /**
     * Sends a cURL POST of the request properties as an http_query
     * Returns a Response object populated from the server's response
     */
    public function sendTransactionRegistrationRequest(Request $req)
    {
        $req->setVpsProtocol($this->vpsProtocol);
        $req->setVendor($this->vendor);

        if ($this->isRoute($this->notificationUrl)) {
            $req->setNotificationUrl($this->convertRouteToAbsoluteUrl($this->notificationUrl));
        } else {
            $req->setNotificationUrl($this->notificationUrl);
        }

        $errors = $this->validator->validate($req);
        if (count($errors)) {
            throw new \Exception('Request failed validation.');
        }

        $curlSession = curl_init();
        curl_setopt_array(
            $curlSession,
            array(
                CURLOPT_URL             =>  $this->url,
                CURLOPT_HEADER          =>  false,
                CURLOPT_POST            =>  true,
                CURLOPT_POSTFIELDS      =>  $req->getQueryString(),
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
            throw new \Exception($error);
        }

        return new Response($response);
    }

    public function createNotification($string)
    {
        $notification = new Notification($string);
        // validate the notification
        $errors = $this->validator->validate($notification);
        if (count($errors)) {
            throw new \IllegalArgumentException();
        }

        return $notification;
    }

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

    public function createNotificationResponse($status, $statusDetail = null)
    {
        $redirectUrl = $this->redirectUrls[strtolower($status)];
        if ($this->isRoute($redirectUrl)) {
            $redirectUrl = $this->convertRouteToAbsoluteUrl($redirectUrl);
        }

        return new NotificationResponse($status, $redirectUrl, $statusDetail);
    }
}