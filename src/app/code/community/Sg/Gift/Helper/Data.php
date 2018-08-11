<?php
class Sg_Gift_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getSendAsGiftUrl($product) {
        $base_url = Mage::app()->getStore()->getBaseUrl();
        $url = "{$base_url}checkout/onepage/?sg=1";
        return Mage::helper('checkout/cart')->getAddUrl($product, array());
    }

    public function getMagicLinkUrl($code) {
        $base_url = Mage::getSingleton('sggift/config')->getMagicLinkBaseUrl();
        return "{$base_url}/{$code}";
    }

    public function getSwiftGiftInfoForOrder($order) {
        if (!$order->getId()) {
            throw new Sg_Gift_Exception('Cant get order id');
        }
        if (!$order->getSwiftGiftUsed()) {
            throw new Sg_Gift_Exception('Order is not using SwiftGift.');
        }
        $gift = Mage::getModel('sggift/gift')->load($order->getId(), 'order_id');
        $layout = Mage::getModel('core/layout');
        $b = $layout->createBlock('core/template');
        $template = Mage::getConfig()->getNode('global/swiftgift/template')->asArray();
        $b
            ->setTemplate($template['order_swift_gift_info'])
            ->setGift($gift->getId() ? $gift : null);
        return $b->toHtml();
    }

}
