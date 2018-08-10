<?php
class Sg_Gift_Model_Resource_Gift_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {
    
    protected function _constuct() {
        parent::__construct();
        $this->_init('sggift/gift');
    }
}