<?php
class Sg_Gift_Api_Client {

    protected $base_url;
    protected $access_token;
    
    public function __construct($base_url) {
        $this->base_url = $base_url;
    }

    public function setAccessToken($access_token) {
        $this->access_token = $access_token;
    }

    public function getAccessToken() {
        return $this->access_token;
    }

    public function authenticate($credentials) {
        Mage::log("swiftgift:client:auth:start: With credentials: " . json_encode($credentials));
        $login_url = '/v1/users/login';
        $c = new Zend_Rest_Client($this->base_url);
        $r = $c->restPost(
            $login_url,
            json_encode($credentials)
        );
        $r_status = (string)$r->getStatus();
        $r_data = json_decode($r->getBody());
        Mage::log("swiftgift:client:auth:result: status:" . $r_status . ", Body: " . $r->getBody());
        if ($r_status === '200') {
            $this->setAccessToken($r_data->auth->access_token);
            Mage::log("swiftgift:client:auth:result: Set access token: " . $r_data->auth->access_token);
        } else {
            throw new Sg_Gift_Api_Exception('unknown', $r_data);
        }
        return $this;
    }

    public function createGift($data) {
        $client = new Zend_Http_Client();
        if ($this->getAccessToken()) {
            $client->setHeaders('Authorization', 'Bearer '.$this->getAccessToken());
            Mage::log("swiftgift:creategift:start setaccesstoken: " . $this->getAccessToken());
        }
        $url = $this->base_url.'/v1/gifts/';
        Mage::log("swiftgift:creategift:start request: Url: " . $url . " Data: " . json_encode($data));
        $r = $client
           ->setUri($url)
           ->setRawData(json_encode($data))
           ->setMethod('POST')
           ->request();
        $r_status = $r->getStatus();
        $r_data = json_decode($r->getBody());
        Mage::log("swiftgift:creategift:result Status: " . $r_status . " Body: " . $r->getBody());
        if (in_array($r_status, array(100, 200, 201), FALSE)) {
            Mage::log("swiftgift:creategift:result Status valid " . $r_status);
            return $r_data;
        } else {
            Mage::log("swiftgift:creategift:result Status not valid " . $r_status);
            throw new Sg_Gift_Api_Exception(($r_status == 400 ? 'validation' : 'unknown'), $r_data);
        }
    }
    
}
