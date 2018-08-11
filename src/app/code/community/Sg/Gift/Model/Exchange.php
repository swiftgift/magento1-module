<?php
class Sg_Gift_Model_Exchange {

    protected $_config;

    public function __construct($config) {
        $this->_config = $config;
    }

    public function getConfig() {
        return $this->_config;
    }

    public function createGift($client, $quote, $order) {
        $base_url = $this->getConfig()->getBaseUrl();
        $gift_data = $this->makeGiftData($quote, $order);
        $order_id = $order->getId();
        $protect_code = $this->makeProtectCode(array('id'=>$order_id));
        $gift_data['idempotency_key'] = $order->getProtectCode();
        $gift_data['callback_url'] = "{$base_url}/sggift/api/cb/id/{$order_id}/code/{$protect_code}";
        $gift_result_data = $this->getCreateGiftValidData(
            (array)$client->createGift($gift_data)
        );
        $gift = Mage::getModel('sggift/gift');
        $gift->setOrderId($order->getId());
        $gift->setStatus($gift_result_data['status']);
        $gift->refreshStatusChangeTime();
        $gift->setCode($gift_result_data['code']);
        $gift->setShareUrl($gift_result_data['share_url']);
        $gift->save();
        return $gift;
    }

    public function handleSwiftGiftCallback($params, $post_data_raw) {
        Mage::log("swiftgift:handlecb:start Raw data: " . $post_data_raw);
        $data = $this->getUpdateCallbackValidData($params, $post_data_raw);
        $data_params = $data['params'];
        $gift_data = $data['gift'];
        if ($this->protectCodeIsValid(array(
            'code'=>$data_params['code'],
            'id'=>$data_params['id']
        ))) {
            Mage::log("swiftgift:handlecb: protect code valid: " . $data_params['code']);
            $gift = Mage::getModel('sggift/gift')->load($data_params['id'], 'order_id');
            if (!$gift->getId()) {
                throw new Sg_Gift_Exception("Cant load gift for order: '{$f_params->id}'");
            }

            $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
            $conn->beginTransaction();

            try {                
                $this->updateGift($gift, $gift_data);
                Mage::log("swiftgift:handlecb: updated gift: " . $gift->getId() . " with data: " . json_encode($gift_data));
            } catch (Exception $e) {
                $conn->rollback();
                Mage::log("swiftgift:handlecb: cant update gift. " . $gift->getId() . " with data: " . json_encode($gift_data));
                throw $e;
            }
            $conn->commit();
        } else {
            Mage::log("swiftgift:handlecb: protect code NOT valid: " . $data_params['code']);
            throw new Sg_Gift_Validation_Exception(
                "Protect code not valid",
                "swift_gift_update_cb_protect_code_invalid"
            );
        }
        return $gift;
    }

    public function updateGift($gift, $gift_data) {
        $o = $gift->getOrder();
        if (!$o->getId()) {
            throw new Sg_Gift_Exception("Cant get order");
        }
        $gift->setStatus($gift_data['status']);
        $gift->refreshStatusChangeTime();
        $gift->save();

        if ($gift->getStatus() === 'accepted') {
            $addr = $o->getShippingAddress();
            $street_addr_data = array($gift_data['delivery_address']['street_address1'], $gift_data['delivery_address']['street_address2']);
            $street_addr = implode(" ", array_filter($street_addr_data));
            $addr
                ->setFirstname($gift_data['delivery_address']['first_name'])
                ->setLastname($gift_data['delivery_address']['last_name'])
                ->setCity($gift_data['delivery_address']['city'])
                ->setStreet($street_addr)
                ->setPostcode($gift_data['delivery_address']['postcode'])
                ->setTelephone("+{$gift_data['delivery_address']['phone_number']['prefix']}{$gift_data['delivery_address']['phone_number']['number']}");
            $addr_validate_result = $addr->validate();
            if ($addr_validate_result !== true) {
                throw new Sg_Gift_Validation_Exception(
                    'SwiftGift shipping address not valid.',
                    'sg_gift_address_not_valid',
                    $addr_validate_result
                );
            }
            $addr->save();
        }
    }

    public function makeGiftData($quote, $order) {
        $gift_data = array(
            'sender'=> array(
                "first_name"=> $quote->getSwiftGiftName(),
                "last_name"=> null,
                "image_url"=> null,
                "email"=> $quote->getCustomerEmail(),
                "phone_number"=> '',
                'billing'=>array_intersect_key($order->getBillingAddress()->getData(), array_flip(array(
                    'firstname', 'lastname', 'email', 'street', 'city', 'telephone', 'country_id', 'company', 'region_id'
                )))
            ),
            "message"=> array(
                "text"=> $quote->getSwiftGiftMessage(),
                "image_url"=> null
            ),
            "is_surprise"=> false,
            "products"=> array_map(array($this, 'getProductData'), $order->getAllVisibleItems()),
            "basket_amount"=> $order->getGrandTotal(),
            "currency"=> $order->getGlobalCurrencyCode(),
            "delivery"=> array(
                "country"=> $quote->getShippingAddress()->getCountryId(),
                "name"=> "DHL Standard Delivery",
                "min_time"=> 1,
                "max_time"=> 2
            )
        );
        return $gift_data;
    }

    public function getProductData($item) {
        return array(
            "name"=>$item->getProduct()->getName(),
            "image_url"=>$item->getProduct()->getSmallImageUrl()
        );
    }

    public function makeProtectCode($data) {
        $key = $this->getConfig()->getEncryptionKey();
        return md5("{$data['id']}{$key}");
    }

    public function protectCodeIsValid($data) {
        return $this->makeProtectCode($data) === $data['code'];
    }

    public function getCreateGiftValidData($data) {
        $f = new Zend_Filter_Input(array(), array(
            'status'=>array(
                array('NotEmpty'),
                array('InArray', array('haystack'=>array('pending')))
            ),
            'code'=>array(
                array('NotEmpty')
            ),
            'share_url'=>array(
                array('NotEmpty')
            )            
        ), $data, array(Zend_Filter_Input::PRESENCE=>Zend_Filter_Input::PRESENCE_REQUIRED));
        if (!$f->isValid()) {
            throw new Sg_Gift_Validation_Exception(
                'Create gift response not valid',
                'swift_gift_create_gift_not_valid',
                $f->getErrors()
            );
        }
        return $f->getEscaped();
    }

    public function getUpdateCallbackValidData($params, $post_raw_data) {
        $default_options = array(Zend_Filter_Input::PRESENCE=>Zend_Filter_Input::PRESENCE_REQUIRED);
        $f_params = new Zend_Filter_Input(array(), array(
            'id'=>array(
                array('NotEmpty')
            ),
            'code'=>array(
                array('NotEmpty')
            )
        ), $params, $default_options);
        if (!$f_params->isValid()) {
            throw new Sg_Gift_Validation_Exception(
                'Update callback data not valid',
                'swift_gift_update_cb_params',
                $f_params->getErrors()
            );
        }
        $data_params = $f_params->getEscaped();
        try {
            $data = json_decode($post_raw_data, true);
        } catch (Exception $e) {
            throw new Sg_Gift_Validation_Exception(
                'Cant parse json data from SwiftGift',
                'swift_gift_update_cb_json_decode',
                array()
            );
        }
        $f_basic = new Zend_Filter_Input(array(), array(
            'status'=>array(
                array('NotEmpty')
            )
        ), $data, $default_options);
        if (!$f_basic->isValid()) {
            throw new Sg_Gift_Validation_Exception(
                'Basic data not valid',
                'swift_gift_update_cb_basic',
                $f_basic->getErrors()
            );
        }
        $data_basic = $f_basic->getEscaped();
        $data_delivery_address = array();
        if ($data_basic['status'] === 'accepted') {
            $f_delivery_address = new Zend_Filter_Input(array(), array(
                'first_name'=>array(
                    array('NotEmpty')
                ),
                'last_name'=>array(
                    array('NotEmpty')
                ),
                'city'=>array(
                    array('NotEmpty')
                ),
                'street_address1'=>array(
                    array('NotEmpty')
                ),
                'street_address2'=>array(
                    array('NotEmpty')
                ),
                'postcode'=>array(
                    array('NotEmpty')
                ),
                'phone_number'=>array(
                    array('NotEmpty')
                )
            ), (isset($data['delivery_address']) ? (array)$data['delivery_address'] : array()), $default_options);
            if (!$f_delivery_address->isValid()) {
                throw new Sg_Gift_Validation_Exception(
                    'Delivery address not valid',
                    'swift_gift_update_cb_delivery_address',
                    $f_delivery_address->getErrors()
                );
            }
            $data_delivery_address = $f_delivery_address->getEscaped();
            $f_phone = new Zend_Filter_Input(array(), array(
                'prefix'=>array(
                    array('NotEmpty'),
                ),
                'number'=>array(
                    array('NotEmpty'),
                )
            ), $data_delivery_address['phone_number'], $default_options);
            if (!$f_phone->isValid()) {
                throw new Sg_Gift_Validation_Exception(
                    'Delivery address phone not valid',
                    'swift_gift_update_cb_delivery_address_phone',
                    $f_phone->getErrors()
                );
            }
            $data_delivery_address['phone_number'] = $f_phone->getEscaped();
        }
        return array(
            'gift'=>array(
                'status'=>$data_basic['status'],
                'delivery_address'=>$data_delivery_address
            ),
            'params'=>$data_params
        );
    }

}
