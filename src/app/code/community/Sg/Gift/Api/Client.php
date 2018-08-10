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
        $login_url = '/v1/users/login';
        $c = new Zend_Rest_Client($this->base_url);
        $r = $c->restPost(
            $login_url,
            json_encode($credentials)
        );
        $r_status = (string)$r->getStatus();
        $r_data = json_decode($r->getBody());
        if ($r_status === '200') {
            $this->setAccessToken($r_data->auth->access_token);
        } else {
            throw new Sg_Gift_Api_Exception('unknown', $r_data);
        }
        return $this;
    }

    public function createGift($data) {
        $client = new Zend_Http_Client();
        if ($this->getAccessToken()) {
            $client->setHeaders('Authorization', 'Bearer '.$this->getAccessToken());
        }
        $url = $this->base_url.'/v1/gifts/';
        $r = $client
           ->setUri($url)
           ->setRawData(json_encode($data))
           ->setMethod('POST')
           ->request();
        $r_status = $r->getStatus();
        $r_data = json_decode($r->getBody());
        if (in_array($r_status, array(100, 200, 201), FALSE)) {
            return $r_data;
        } else {
            throw new Sg_Gift_Api_Exception(($r_status == 400 ? 'validation' : 'unknown'), $r_data);
        }
    }
    
}
