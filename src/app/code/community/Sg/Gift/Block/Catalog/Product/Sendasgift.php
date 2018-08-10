<?php
class Sg_Gift_Block_Catalog_Product_Sendasgift extends Mage_Core_Block_Template {

    public function canShow() {
        return Mage::helper('checkout')->canOnepageCheckout();
    }
    
    public function getSendAsGiftUrl() {
        return Mage::helper('sggift')->getSendAsGiftUrl($this->getProduct());
    }
    
}