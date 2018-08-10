<?php
class Sg_Gift_Block_Adminhtml_Dashboard_Sales extends Mage_Adminhtml_Block_Dashboard_Sales {
    protected function _prepareLayout() {
        parent::_prepareLayout();
        $gifts_all = Mage::getModel('sggift/gift')->getCollection();
        $gifts_completed = Mage::getModel('sggift/gift')->getCollection()->addFieldToFilter('status', 'accepted');
        $this->addTotal('SwiftGift magic links created', $gifts_all->count(), true);
        $this->addTotal('SwiftGift magic links used', $gifts_completed->count(), true);
    }
}
