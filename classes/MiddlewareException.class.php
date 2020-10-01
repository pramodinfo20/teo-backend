<?php

use Psr\Http\Message\ResponseInterface;

class MiddlewareException extends Exception
{
    /**
     * var string
     */
    private $response;

    public function __construct(
        string $message = '',
        int $code = 0,
        Throwable $previous = null,
        ResponseInterface $response = null)
    {
        parent::__construct($message, $code, $previous);

        $this->response = $response;
    }

    /**
     * Representation of an outgoing, Symfony error response.
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}