<?php
class Sg_Gift_Model_Config {

    public function getEncryptionKey() {
        return Mage::getConfig()->getNode('global/crypt/key')->__toString();
    }    

    public function getBaseUrl() {
        return trim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB), '/');
    }

    public function getUserEmail() {
        return Mage::getStoreConfig('secsggiftconf/general/user_email');
    }

    public function getUserPassword() {
        return Mage::getStoreConfig('secsggiftconf/general/user_password');
    }

    public function getSgApiCredentials() {
        return array(
            'email'=>$this->getUserEmail(),
            'password'=>$this->getUserPassword()
        );
    }
    
    public function getSgApiBaseUrl() {
        return trim(Mage::getStoreConfig('secsggiftconf/general/sg_api_base_url'), '/');
    }

    public function getMagicLinkBaseUrl() {
        return trim(Mage::getStoreConfig('secsggiftconf/general/magic_link_base_url'), '/');
    }

    public function getSgApiClientId() {
        return Mage::getStoreConfig('secsggiftconf/general/sg_api_client_id');
    }

    public function getDefaultMessageImageUrl() {
        $val = Mage::getStoreConfig('secsggiftconf/general/sg_message_image_url');
        if (substr($val, 0, 4 ) !== "http") {
            $val = "{$this->getBaseUrl()}/$val";
        }
        return $val;
    }

    public function getSenderImageUrl() {
        return Mage::getStoreConfig('secsggiftconf/general/sender_image_url');
    }
    
}
