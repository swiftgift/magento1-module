<?php
class Sg_Gift_Block_Catalog_Sendasgift_Productview_Button extends Mage_Core_Block_Template {
    public function getProduct() {
        return Mage::registry('product');
    }

    public function _toHtml() {
        return $this->getLayout()->createBlock('sggift/catalog_sendasgift_productview')->setProduct($this->getProduct())->toHtml();
    }
}