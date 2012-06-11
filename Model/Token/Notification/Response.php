<?php

namespace Insig\SagepayBundle\Model\Token\Notification;

use Symfony\Component\Validator\Constraints as Assert;

use Insig\SagepayBundle\Model\Base\NotificationResponse as BaseNotificationResponse;

/**
 * Implemented according to the Sagepay Server Protocol and Integration
 * Guideline (Protocol version 2.23)
 *
 * A4: You acknowledge receipt of the Notification POST
 * This is the plain text response part of the POST originated by the Server
 * in the step above (A3). Encoding must be as Name=Value fields separated by
 * carriage-return-linefeeds (CRLF).
 *
 * @author Damon Jones
 */
class Response extends BaseNotificationResponse
{
    // Alphabetic. Max 20 characters. OK, MALFORMED, INVALID OR ERROR ONLY.
    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"OK", "MALFORMED", "INVALID", "ERROR"})
     */
    protected $status;
}
