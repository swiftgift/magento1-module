<?php
class Sg_Gift_Block_Catalog_Sendasgift_Productlist extends Mage_Core_Block_Template {
    protected $_template = 'sggift/catalog/sendasgift/product_list.phtml';

    public function getSendAsGiftUrl() {
        return Mage::helper('sggift')->getSendAsGiftUrl($this->getProduct());
    }
    
}