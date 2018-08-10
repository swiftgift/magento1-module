<?php
class Sg_Gift_Block_Catalog_Sendasgift_Productview extends Mage_Core_Block_Template {
    protected $_template = 'sggift/catalog/sendasgift/product_view.phtml';

    public function getSendAsGiftUrl() {
        return Mage::helper('sggift')->getSendAsGiftUrl($this->getProduct());
    }    
    
}