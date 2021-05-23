<?php

namespace MostWebFramework\Client;
use Exception;

class Args {

    /**
     * @param * $arg
     * @param string $name
     * @throws Exception
     */
    public static function notNull($arg, $name) {
        if (is_null($arg)) {
            throw new Exception($name." may not be null");
        }
    }

    /**
     * @param * $arg
     * @param string $name
     * @throws Exception
     */
    public static function notString($arg, $name) {
        if (!is_string($arg)) {
            throw new Exception($name." must be a string");
        }
    }


    /**
     * @param * $arg
     * @param string $name
     * @throws Exception
     */
    public static function notEmpty($arg, $name) {
        Args::notNull($arg,$name);
        Args::notString($arg,$name);
        if (strlen($arg)==0) {
            throw new Exception($name." may not be empty");
        }
    }

    /**
     * @param * $arg
     * @param string $message
     * @throws Exception
     */
    public static function check($arg, $message) {
        if (!$arg) {
            throw new Exception($message);
        }
    }
}
