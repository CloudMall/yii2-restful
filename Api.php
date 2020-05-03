<?php

namespace cloudMall\rest;

class Api {

    public $productLineName = "offline";

    public $secret = "offline_secret";

    public $host = "http://api.cloudmall.com/";

    public $hostReadOnly = "http://api.cloudmall.com/";
    
    /**
     * 如果需要更换产品线，继承这个类并重载构造函数
     */
    public function __construct() {
    }
    
    public function getProductLine() {
        return array("name"=>$this->productLineName, "secret"=>$this->secret);
    }
    
    public function setProductLine($name, $secret) {
        $this->productLineName = $name;
        $this->secret = $secret;
    }

    public function getHost() {
        return $this->host;
    }

    public function setHost($host) {
        $this->host = $host;
    }

    public function post($url, $params=array(), $signMethod="signAndTimeStamp") {
        $params["tpl"] = $this->productLineName;
        $query = "";
        if (YII_DEBUG) {
            $params["x_offline_debug"] = 1;
            $query = "?x_offline_debug=1";
        }
        if ($signMethod) {
            $params = $this->$signMethod($params);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->host . $url . $query);
        curl_setopt($ch, CURLOPT_POST, 1);
        $postString = json_encode($params);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        // pretend request as ajax to avoid debug log
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Requested-With: XMLHttpRequest",
            "Content-Type: application/json", 'Content-Length: ' . strlen($postString)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        return json_decode($result, true);
    }

    public function put($url, $params=array(), $signMethod="signAndTimeStamp") {
        $params["tpl"] = $this->productLineName;
        $query = "";
        if (YII_DEBUG) {
            $params["x_offline_debug"] = 1;
            $query = "?x_offline_debug=1";
        }
        if ($signMethod) {
            $params = $this->$signMethod($params);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->host . $url . $query);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        $postString = json_encode($params);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        // pretend request as ajax to avoid debug log
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Requested-With: XMLHttpRequest",
            "Content-Type: application/json", 'Content-Length: ' . strlen($postString)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        return json_decode($result, true);
    }

    public function get($url, $params=array(), $signMethod="signAndTimeStamp") {
        $params["tpl"] = $this->productLineName;
        if (YII_DEBUG) {
            $params["x_offline_debug"] = 1;
        }
        if ($signMethod) {
            $params = $this->$signMethod($params);
        }

        $query = "?";
        foreach ($params as $key=>$value) {
            if (is_array($value)) {
                $query .= rawurlencode($key) . "=" . rawurlencode(json_encode($value));
            } else {
                $query .= rawurlencode($key) . "=" . rawurlencode($value);
            }
            $query .= "&";
        }
        $query = substr($query, 0, strlen($query) - 1);

        $ch = curl_init();
        $host = $this->hostReadOnly;
        if (!$host) {
            $host = $this->host;
        }
        curl_setopt($ch, CURLOPT_URL, $host . $url . $query);
        // pretend request as ajax to avoid debug log
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Requested-With: XMLHttpRequest"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $result = curl_exec($ch);
        return json_decode($result, true);
    }

    /**
     * 时间戳与签名验证： (推荐)
     */
    protected function signAndTimeStamp($params) {
        $params["timestamp"] = time();
        ksort($params);
        $sign = md5($this->secret . preg_replace('/"/', "", json_encode($params)));
        $params["sign"] = $sign;
        return $params;
    }

    /**
     * 签名验证
     */
    protected function sign($params) {
        ksort($params);
        $sign = md5($this->secret . preg_replace('/"/', "", json_encode($params)));
        $params["sign"] = $sign;
        return $params;
    }

    /**
     * 明文密钥验证
     */
    protected function secret($params) {
        $params["secret"] = $this->secret;
        return $params;
    }
}

