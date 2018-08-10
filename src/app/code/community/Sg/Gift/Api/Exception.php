<?php
class Sg_Gift_Api_Exception extends Sg_Gift_Exception {
    public $code;
    public $data;
    public function __construct($code, $data, $message=NULL) {
        $this->code = $code;
        $this->data = $data;
        parent::__construct($message);
    }
}