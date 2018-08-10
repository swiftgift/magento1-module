<?php
class Sg_Gift_ApiController extends Mage_Core_Controller_Front_Action {
    
    public function cbAction() {
        $r = $this->getRequest();
        $order_id = $r->getParam('id');
        $protect_code = $r->getParam('code');
        $data_raw = file_get_contents('php://input');
        $config = Mage::getSingleton('sggift/config');
        $exchange = new Sg_Gift_Model_Exchange($config);
        $response_code = 200;
        $response_content = 'OK';
        try {
            $g = $exchange->handleSwiftGiftCallback(
                array(
                    'id'=>$order_id,
                    'code'=>$protect_code
                ),
                $data_raw
            );
        } catch (Exception $e) {
            Mage::logException($e);
            $response_status = '500';
            $response_content = 'Cant update gift info.';
        }
        $this->getResponse()
            ->setHttpResponseCode($response_code)
            ->setBody($response_content);
    }
}