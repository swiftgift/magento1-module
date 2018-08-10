<?php
class Sg_Gift_Model_Sales_Service_Quote extends Mage_Sales_Model_Service_Quote {
    protected function _validate()
    {
        if ($this->getQuote()->getSwiftGiftUsed()) {
            return Mage::getSingleton('sggift/checkout')->validateServiceQuoteWithoutShippingAddress($this);
        } else {
            return parent::_validate();
        }
    }
}