<?php
class Sg_Gift_Block_Adminhtml_Sales_Order_View_Tab_Swiftgift extends Mage_Adminhtml_Block_Sales_Order_Abstract implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    protected $_gift;

    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function getGift() {
        if (!$this->_gift) {
            $order = $this->getOrder();
            if ($order && $order->getId()) {
                $gift = Mage::getModel('sggift/gift')->load($order->getId(), 'order_id');
                if ($gift && $gift->getId()) {
                    $this->_gift = $gift;
                }
            }
        }
        return $this->_gift;
    }

    public function getSource()
    {
        return $this->getOrder();
    }

    public function getTabLabel() {
        return 'SwiftGift';
    }

    public function getTabTitle() {
        return 'SwiftGift';
    }

    public function canShowTab() {
        return $this->getOrder()->getSwiftGiftUsed();
    }

    public function isHidden() {
        return false;
    }

}
