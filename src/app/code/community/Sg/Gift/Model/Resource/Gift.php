<?php
class Sg_Gift_Model_Resource_Gift extends Mage_Core_Model_Resource_Db_Abstract {

    protected function _construct() {
        $this->_init('sggift/gift', 'entity_id');
    }
    
}