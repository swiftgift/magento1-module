<?php
class Sg_Gift_Exception extends Exception {

    public $code;
    
    public function __construct($message, $code='unknown') {
        $this->code = $code;
        parent::__construct($message);
    }
}