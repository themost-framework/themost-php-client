<?php

namespace MostWebFramework\Client;

/**
 * A FilterExpression instance that is going to be used in open data filter statements
 * Class FilterExpression
 */
class FilterExpression {

    public $expr;
    /**
     * @param string $expr
     */
    public function __construct($expr) {
        $this->expr = $expr;
    }
    public function __toString(){
        return $this->expr;
    }
}