<?php

namespace MostWebFramework\Client;

/**
 * Represents a common HTTP exception
 * Class HttpClientException
 */
class HttpClientException extends Exception {

    // Redefine the exception so message isn't optional
    public function __construct($message = null, $code = 500, Exception $previous = null) {
        // make sure everything is assigned properly
        if (is_null($message))
            $message = 'Internal Server Error';
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return "[{$this->code}] {$this->message}";
    }
}