<?php
class Sg_Gift_Model_Resource_Exchange extends Mage_Core_Model_Resource_Db_Abstract {

    protected function _construct() {
        $this->_init('sggift/exchange', 'entity_id');
    }
    
}