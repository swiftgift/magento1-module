<?php
class Sg_Gift_Validation_Exception extends Sg_Gift_Exception {

    protected $errors;
    
    public function __construct($message, $code='validation', $errors=array()) {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getErrorsAsJson() {
        return json_encode($this->getErrors());
    }

    public function __toString() {
        $errors_str = $this->getErrorsAsJson();
        return parent::__toString() . " Validation errors: {$errors_str}.";
    }
    
}