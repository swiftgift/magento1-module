<?php
class Sg_Gift_Block_Address_Renderer extends Mage_Customer_Block_Address_Renderer_Default {
    public function render(Mage_Customer_Model_Address_Abstract $address) {
        return Mage::helper('sggift')->getSwiftGiftInfoForOrder($address->getOrder());
    }
}