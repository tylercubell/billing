<?php

namespace tylercubell\Billing\Exceptions;

use Exception;

class BillingException extends Exception
{
    /**
     * Stripe error HTTP status.
     *
     * @var string
     */
    public $status;

    /**
     * Stripe error type.
     *
     * @var string
     */
    public $type;

    /**
     * Stripe error code.
     *
     * @var string
     */
    public $code;

    /**
     * Stripe error param.
     *
     * @var string
     */
    public $param;

    /**
     * Stripe error message.
     *
     * @var string
     */
    public $message;

    /**
     * Create a new exception instance.
     *
     * @param $e
     * @return void
     */
    public function __construct($e)
    {
        $body  = $e->getJsonBody();
        $error = $body['error'];

        $this->status  = $e->getHttpStatus();
        $this->type    = isset($error['type']) ? $error['type'] : null;
        $this->code    = isset($error['code']) ? $error['code'] : null;
        $this->param   = isset($error['param']) ? $error['param'] : null;
        $this->message = isset($error['message']) ? $error['message'] : null;
    }
}
