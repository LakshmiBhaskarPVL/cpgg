<?php

namespace App\Exceptions\Payment;

use Exception;

/**
 * Base exception for payment flow failures.
 */
class PaymentException extends Exception
{
    /**
     * The HTTP status code to return to the client, if applicable.
     *
     * @var int
     */
    protected int $statusCode;

    /**
     * @param  string  $message
     * @param  int  $statusCode
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = '', int $statusCode = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->statusCode = $statusCode;
    }

    /**
     * Get the HTTP status code for this exception.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get a message that is safe to show to end users.
     *
     * Payment exceptions already carry business-oriented messages, but the
     * method is kept for uniformity with the rest of the hierarchy so future
     * "raw" subclasses can override it without changing the renderers.
     *
     * @return string
     */
    public function getPublicMessage(): string
    {
        return $this->getMessage();
    }
}
