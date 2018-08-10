<?php
class Sg_Gift_Block_Checkout_Onepage_Link extends Mage_Core_Block_Template {

    public function canShow() {
        return Mage::helper('checkout')->canOnepageCheckout();
    }

    public function getSwiftGiftCheckoutLink() {
        return '/checkout/onepage/?sg=1';
    }
    
}