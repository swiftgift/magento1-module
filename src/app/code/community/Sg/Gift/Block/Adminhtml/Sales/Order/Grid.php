<?php
class Sg_Gift_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid {

    protected function _preparePage() {
        parent::_preparePage();
        $order = Mage::getModel('sales/order');
        $collection = $this->getCollection();
        if ($collection) {
            $collection->getSelect()
                ->joinInner(array(
                    'order'=>Mage::getSingleton('core/resource')->getTableName('sales/order'),
                ), 'main_table.entity_id=order.entity_id', array(
                    'order.swift_gift_used'
                ))
                ->joinLeft(array(
                    'gift'=>Mage::getSingleton('core/resource')->getTableName('sggift/gift')
                ), 'gift.order_id=order.entity_id', array(
                    'gift.status as gift_status'
                ))
                ;
        }
    }

    protected function _prepareColumns() {
        $this->addColumnAfter('swift_gift_used', array(
            'header'=>'SwiftGift used',
            'index'=>'swift_gift_used',
            'type'=>'text',
            'getter'=>array($this, 'getSwiftGiftUsed'),
            'filter'=>false
        ), "status");
        return parent::_prepareColumns();
    }

    public function getSwiftGiftUsed($item) {
        $result_str = $this->_valueYesNo($item->getSwiftGiftUsed());
        if ($item->getGiftStatus()) {
            $status_str = Mage::getSingleton('sggift/gift')->getStatusRepr($item->getGiftStatus());
            $result_str .= ", {$status_str}";
        }
        return $result_str;
    }

    public function getSwiftGiftComplete($item) {
        return $this->_valueYesNo($item->getSwiftGiftComplete());
    }

    protected function _valueYesNo($val) {
        $value = $val ? 'Yes' : 'No';
        return $value;
    }

}
