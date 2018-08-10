<?php
class Sg_Gift_Block_Checkout_Success extends Mage_Core_Block_Template {

    protected $_order;
    protected $_gift;

    protected function _getOrder() {
        if (!$this->_order) {
            $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
            if ($orderId) {
                $this->_order = Mage::getModel('sales/order')->load($orderId);
            }
        }
        return $this->_order;
    }
    
    public function getOrder() {
        return $this->_getOrder();
    }

    public function getGift() {
        if (!$this->_gift) {
            $order = $this->getOrder();
            if ($order && $order->getId() && $order->getSwiftGiftUsed()) {
                
                if ($gift->getId()) {
                    $this->_gift = $gift;
                }
            }
        }
        return $this->_gift;
    }

    public function getGiftHtml() {
        $order = $this->getOrder();
        $gift = Mage::getModel('sggift/gift')->load($order->getId(), 'order_id');
        $b = $this
           ->getLayout()
           ->createBlock('core/template');
        if ($gift->getId()) {
            return $b
                ->setTemplate('sggift/checkout/info.phtml')
                ->setOrder($order)
                ->setGift($gift)
                ->toHtml();
        } else {
            return $b
                ->setTemplate('sggift/order/cant_get_gift.phtml')
                ->setOrder($order)
                ->setGift(null)
                ->toHtml();
        }
    }

    public function getShowGift() {
        $order = $this->getOrder();
        return $order && $order->getSwiftGiftUsed();
    }
    
}