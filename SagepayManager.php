<?php

namespace Insig\SagepayBundle;

use Insig\SagepayBundle\TransactionRegistration\Request;
use Insig\SagepayBundle\TransactionRegistration\Response;

class SagepayManager
{
    protected $validator;
    protected $router;

    protected $url;
    protected $vpsProtocol;
    protected $vendor;
    protected $notificationUrl;
    protected $redirectUrl;

    protected function convertRouteToAbsoluteUrl($route)
    {
        return $this->router->generate(substr($route, 1), array(), true);
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

    public function setUrl($url)
    {
        $this->url = $url;
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
     * If the notification URL starts with an "@" then treat it as a route
     * name and generate the absolute URL from the route name
     */
    public function setNotificationUrl($notificationUrl)
    {
        if ('@' === $notificationUrl[0]) {
            $notificationUrl = $this->convertRouteToAbsoluteUrl($notificationUrl);
        }
        $this->notificationUrl = $notificationUrl;
    }

    /**
     * If the redirect URL starts with an "@" then treat it as a route
     * name and generate the absolute URL from the route name
     */
    public function setRedirectUrl($redirectUrl)
    {
        if ('@' === $redirectUrl[0]) {
            $redirectUrl = $this->convertRouteToAbsoluteUrl($redirectUrl);
        }
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * Sends a cURL POST of the request properties as an http_query
     * Returns a Response object populated from the server's response
     */
    public function sendTransactionRegistrationRequest(Request $req)
    {
        $req->setVpsProtocol($this->vpsProtocol);
        $req->setVendor($this->vendor);
        $req->setNotificationUrl($this->notificationUrl);

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
}