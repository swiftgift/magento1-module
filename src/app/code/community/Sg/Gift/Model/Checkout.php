<?php
class Sg_Gift_Model_Checkout {

    public function handleCartAddProductComplete($observer) {
        // $data = $observer->getEvent()->getData();
        // $r = $data['request'];
        // $r->setParam('return_url', '/');
    }

    public function handlerAfterSaveBilling($observer) {
        $data = $observer->getEvent()->getData();
        $controller = $data['controller_action']; 
        $request = $controller->getRequest();
        $q = $controller->getOnePage()->getQuote();
        $data = $request->getPost();
        $swift_gift_used = $data && isset($data['swift_gift_used']) && $data['swift_gift_used'] === '1';
        $this->setQuoteSwiftGiftUsed($q, $swift_gift_used);
        $q->save();
        if ($swift_gift_used) {
            $controller->getOnepage()->getCheckout()->setStepData('shipping', 'complete', false);
        }
    }

    public function handlerBeforeCheckoutInit($observer) {
        $data = $observer->getEvent()->getData();
        $controller = $data['controller_action'];
        $request = $controller->getRequest();
        $quote = $controller->getOnepage()->getQuote();
        $use_sg = $request->getParam('sg', null);
        $this->setQuoteSwiftGiftUsed(
            $quote,
            ($use_sg === '1')
        );
        $quote->collectTotals();
        $quote->getShippingAddress()->save();
        $quote->save();
    }

    public function handlerAfterOrderSubmit($observer) {
        $config = $this->getConfig();
        $client = new Sg_Gift_Api_Client(
            $config->getSgApiBaseUrl()
        );
        $exchange = new Sg_Gift_Model_Exchange(
            $this->getConfig()
        );
        $data = $observer->getEvent()->getData();
        $order = $data['order'];
        $quote = $data['quote'];
        if ($quote->getSwiftGiftUsed()) {
            try {
                $client->authenticate($config->getSgApiCredentials());
                $gift = $exchange->createGift($client, $quote, $order);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    public function getProductGiftUrl($product) {
        $base_url = Mage::app()->getStore()->getBaseUrl();
        $url = "{$base_url}checkout/onepage/?sg=1";
        return Mage::helper('checkout/cart')->getAddUrl($product, array()) . "?return_url={$url}";
    }

    public function getAvailableCountries() {
        return Mage::getSingleton('directory/country')->getResourceCollection()
            ->loadByStore()->toOptionArray();
    }

    public function getAvailableCountriesCodes() {
        return array_map(array($this, 'extrCountryCode'), $this->getAvailableCountries());
    }

    public function extrCountryCode($option) {
        return $option['value'];
    }
    
    public function validateServiceQuoteWithoutShippingAddress($service_quote) {
        $addressValidation = $service_quote->getQuote()->getBillingAddress()->validate();
        if ($addressValidation !== true) {
            Mage::throwException(
                Mage::helper('sales')->__('Please check billing address information. %s', implode(' ', $addressValidation))
            );
        }
        if (!($service_quote->getQuote()->getPayment()->getMethod())) {
            Mage::throwException(Mage::helper('sales')->__('Please select a valid payment method.'));
        }
    }

    public function setQuoteSwiftGiftUsed($quote, $swift_gift_used) {
        if ($swift_gift_used) {
            $quote->setSwiftGiftUsed(true);
            $quote->getShippingAddress()->setSamesAsBilling(0)->delete()->isDeleted(true);
        } else {
            $quote->setSwiftGiftUsed(false);
        }
    }


    public function setQuoteGiftData($quote, $data) {
        $conf = $this->getConfig();
        if (!$conf->isFilled()) {
            return array('Swift gift settings is not valid. Please change swift gift settings in admin panel.');
        }
        try {
            $data = $this->getCheckoutValidData(
                $this->getAvailableCountriesCodes(),
                $data
            );
        } catch (Sg_Gift_Validation_Exception $e) {
            return $e->getErrors();
        }
        $quote
            ->setSwiftGiftUsed(true)
            ->setSwiftGiftName($data['name'])
            ->setSwiftGiftMessage($data['message']);
        $addr = $quote->getShippingAddress();
        $addr->setCountryId($data['sg_shipping']['country_id']);
        if ($data['sg_shipping']['region_id']) {
            $addr->setRegionId($data['sg_shipping']['region_id']);
        } else {
            $addr->setRegion($data['sg_shipping']['region']);
        }
        $addr->setCollectShippingRates(false);
        $addr->removeAllShippingRates();
        $addr->requestShippingRates();
        return array();
    }

    public function getCheckoutValidData($available_countries_codes, $data) {
        $f = new Zend_Filter_Input(array(), array(
            'name'=>array(
                array('StringLength', array(
                    'min'=>0,
                    'max'=>255
                )),
                'allowEmpty'=>true
            ),
            'message'=>array(
                array('StringLength', array(
                    'min'=>0,
                    'max'=>400
                )),
                'allowEmpty'=>true
            )
        ), $data, array(Zend_Filter_Input::PRESENCE=>Zend_Filter_Input::PRESENCE_REQUIRED));
        if (!$f->isValid()) {
            throw new Sg_Gift_Validation_Exception(
                'Checkout data not valid',
                'swift_gift_checkout_form',
                $f->getErrors()
            );
        }
        $sg_shipping_data = isset($data['sgshipping']) ? (array)$data['sgshipping'] : array();
        $data = $f->getEscaped();
        $data['sg_shipping'] = $this->getSgShippingValidData($sg_shipping_data);
        return $data;
    }

    public function getSgShippingValidData($data) {
        $f = new Zend_Filter_Input(array(), array(
            'country_id'=>array(
                array('NotEmpty'),
                array('InArray', array(
                    'haystack'=>$this->getAvailableCountriesCodes()
                ))   
            ),
            'region_id'=>array(
                'Int',
                'allowEmpty'=>true
            ),
            'region'=>array(
                'allowEmpty'=>true
            )
        ), $data);
        if (!$f->isValid()) {
            throw new Sg_Gift_Validation_Exception(
                'Checkout data not valid',
                'swift_gift_checkout_form_sg_shipping',
                $f->getErrors()
            );
        }
        $data = $f->getEscaped();
        if (!$data['region_id'] && !$data['region']) {
            throw new Sg_Gift_Validation_Exception(
                'Checkout data not valid',
                'swift_gift_checkout_form_sg_shipping',
                array('region_id'=>array('region or region_id must not be empty.'))
            );
        }
        $region_id_required = Mage::helper('directory')->isRegionRequired($data['country_id']);
        if ($region_id_required && !$data['region_id']) {
            throw new Sg_Gift_Validation_Exception(
                'Checkout data not valid',
                'swift_gift_checkout_form_sg_shipping',
                array('region_id'=>array('region_id is required'))
            );
        }
        return $data;
    }

    public function getConfig() {
        return Mage::getSingleton('sggift/config');
    }
    
}






