<?php

class Mugglepay
{
    private $MugglepayAppSecret;
    private $MugglepayGatewayUrl;

    public function __construct($a,$b)
    {
        $this->MugglepayAppSecret = $a;
        $this->MugglepayGatewayUrl = $b;
    }

    public function random($length, $numeric = 0)
    {
        $seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
        $seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
        $hash = '';
        $max = strlen($seed) - 1;
        for($i = 0; $i < $length; $i++) {
            $hash .= $seed[mt_rand(0, $max)];
        }
        return $hash;
    }

    public function isHTTPS()
    {
        define('HTTPS', false);
        if (defined('HTTPS') && HTTPS) {
            return true;
        }
        if (!isset($_SERVER)) {
            return false;
        }
        if (!isset($_SERVER['HTTPS'])) {
            return false;
        }
        if ($_SERVER['HTTPS'] === 1) {  // Apache
            return true;
        }

        if ($_SERVER['HTTPS'] === 'on') { // IIS
            return true;
        }

        if ($_SERVER['SERVER_PORT'] == 443) { //
            return true;
        }

        return false;
    }

    public function prepareSignId($tradeno)
    {
        $data_sign = array();
        $data_sign['merchant_order_id'] = $tradeno;
        $data_sign['secret'] = $this->MugglepayAppSecret;
        ksort($data_sign);
        return http_build_query($data_sign);
    }
    public function sign($data)
    {
        return strtolower(md5(md5($data) . $this->MugglepayAppSecret));
    }
    public function verify($data, $signature)
    {
        $mySign = $this->sign($data);
        return $mySign === $signature;
    }
    public function mprequest($data, $type = 'pay')
    {
        $headers = array('content-type: application/json', 'token: ' . $this->MugglepayAppSecret);
        $curl = curl_init();
        if ($type === 'pay') {
            $mugglepayUrl = $this->MugglepayGatewayUrl . 'orders';
            curl_setopt($curl, CURLOPT_URL, $mugglepayUrl);
            curl_setopt($curl, CURLOPT_POST, 1);
            $data_string = json_encode($data);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        } elseif ($type === 'query') {
            $mugglepayUrl = $this->MugglepayGatewayUrl . 'orders/' . $data['muggle_order_id'];
            curl_setopt($curl, CURLOPT_URL, $mugglepayUrl);
            curl_setopt($curl, CURLOPT_HTTPGET, 1);
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }

    public function query($muggleId)
    {
        $data['muggle_order_id'] = $muggleId;
        return json_decode($this->mprequest($data, 'query'), true);
    }
}
