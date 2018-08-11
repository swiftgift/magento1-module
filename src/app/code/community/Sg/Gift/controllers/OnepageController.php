<?php
require_once Mage::getModuleDir('controllers', "Mage_Checkout").DS."OnepageController.php";
class SG_Gift_OnepageController extends Mage_Checkout_OnepageController {

    public function getSgCheckout() {
        return Mage::getSingleton('sggift/checkout');
    }

    public function getShippingMethodsAction() {
        $this->getResponse()->setBody($this->_getShippingMethodsHtml());
    }

    public function saveGiftAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $quote = $this->getOnepage()->getQuote();
            $errors = $this->getSgCheckout()->setQuoteGiftData(
                $quote,
                $request->getPost()
            );
            if (empty($errors)) {
                $quote->getShippingAddress()->save();
                $quote->save();
                $result = array(
                    'success'=>true
                );
            } else {
                $result = array(
                    'success'=>false,
                    'error'=>true,
                    'message'=>$errors
                );
            }
						$this->getResponse()->setHeader('Content-type', 'application/json', true);
						$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function saveShippingAction() {
        $this->getOnepage()->getQuote()->setSwiftGiftUsed(false)->save();
        return parent::saveShippingAction();
    }
		
}
