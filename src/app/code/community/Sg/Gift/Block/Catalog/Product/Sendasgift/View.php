<?php
class Sg_Gift_Block_Catalog_Product_Sendasgift_View extends Sg_Gift_Block_Catalog_Product_Sendasgift {
    public function getProduct() {
        return Mage::registry('product');
    }
}