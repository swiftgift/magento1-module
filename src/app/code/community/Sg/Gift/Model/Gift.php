<?php
class Sg_Gift_Model_Gift extends Mage_Core_Model_Abstract {

    const SG_ADDR_TYPE = 'swift_gift_shipping';

    protected $_order;

    protected function _construct() {
        $this->_init('sggift/gift');
    }

    public function getOrder() {
        if (!$this->_order) {
            $this->_order = Mage::getModel('sales/order')->load($this->getOrderId());
        }
        return $this->_order;
    }

    public function setOrder($order) {
        $this->_order = $order;
    }

    public function refreshStatusChangeTime() {
        $this->setStatusChangeTime(now());
    }

    public function getMagicLinkUrl() {
        return Mage::helper('sggift')->getMagicLinkUrl($this->getCode());
    }

    public function handleCustomerAddressFormat($observer) {
        $data = $observer->getEvent()->getData();
        $formatType = $data['type'];
        $address = $data['address'];
        $show_sg_for_customer = $address->getAddressType() === Mage_Sales_Model_Order_Address::TYPE_SHIPPING && $address->getOrder() && $address->getOrder()->getSwiftGiftUsed();
        $show_shipping_standard = (bool)Mage::registry('swift_gift_show_shipping_standard');
        if ($show_sg_for_customer && !$show_shipping_standard) {
            $renderer = new Sg_Gift_Block_Address_Renderer();
            $formatType->setRenderer($renderer);
        }
    }

    public function handleShowAdminOrderPage($observer) {
        $data = $observer->getEvent()->getData();
        $controller = $data['controller_action'];
        Mage::register('swift_gift_show_shipping_standard', true);
    }

    public function getStatusRepr($status) {
        $statuses = array(
            'accepted'=>'Complete',
            'pending'=>'Pending'
        );
        return isset($statuses[$status]) ? $statuses[$status] : $status;
    }
    
}