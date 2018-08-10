<?php
class Sg_Gift_Block_Checkout_Onepage_Shipping extends Mage_Checkout_Block_Onepage_Shipping {

    public function isSwiftGift() {
        return $this->getQuote()->getSwiftGiftUsed();
    }

    public function getCountriesHtmlSelect() {
        $countryId = Mage::helper('core')->getDefaultCountry();
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName('country_code')
            ->setId('id_country_code')
            ->setTitle('SwiftGift country')
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setOptions($this->getCountryOptions());
        return $select->getHtml();
    }

    public function getStatesHtmlSelect() {
        
    }

    public function getCountryOptions() {
        return Mage::getModel('sggift/checkout')->getAvailableCountries();
    }
}
