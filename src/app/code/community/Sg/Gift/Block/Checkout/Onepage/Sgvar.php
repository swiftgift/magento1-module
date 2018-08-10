<?php
class Sg_Gift_Block_Checkout_Onepage_Sgvar extends Mage_Core_Block_Template {

    public function getCheckout()
    {
        if (empty($this->_checkout)) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }
        return $this->_checkout;
    }

    /**
     * Retrieve sales quote model
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (empty($this->_quote)) {
            $this->_quote = $this->getCheckout()->getQuote();
        }
        return $this->_quote;
    }

    public function getSwiftGiftUsed() {
        return $this->getQuote()->getSwiftGiftUsed();
    }

}