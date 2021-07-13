<?php
require_once("Core.php");
require_once("Config.php");

$Mugglepay = new Mugglepay($appSecret, $gateWayUrl);

$order_id = $Mugglepay->random(10);
$price = 10.99;
$priceCurrency = 'CNY';
$payCrurrency ='';
$mobile ='';
$title = 'here is the title';
$description = 'here is the description';
$httpxx = ($Mugglepay->isHTTPS() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
$fast = '';
$mugglepay_order_id = '';

// https://github.com/MugglePay/MugglePay/blob/master/API/order/CreateOrder.md
$data['merchant_order_id'] = $order_id;
$data['price_amount'] = (double)$price;
$data['price_currency'] = $priceCurrency;
$data['pay_currency'] = $payCrurrency;
$data['mobile'] = $mobile;
$data['title'] = $title;
$data['description'] = $description;
$data['callback_url'] = $httpxx . '/callback.php?order_id='. $order_id;
$data['success_url'] = $httpxx . '/success.php?order_id='. $order_id;
$data['cancel_url'] = $httpxx . '/cancel.php?order_id='. $order_id;
$data['fast'] = $fast;
$data = array_filter($data);
// var_dump($data);

$str_to_sign = $Mugglepay->prepareSignId($order_id);
$data['token'] = $Mugglepay->sign($str_to_sign);
$result = json_decode($Mugglepay->mprequest($data), true);
var_dump($result);

// Create Order
if ($result['status'] === 200 || $result['status'] === 201)
{
    $result['payment_url'] .= '&lang=zh';
    echo 'Hi!You should click this url to pay your order' . PHP_EOL;
    echo $result['payment_url'];
    echo PHP_EOL;

    $mugglepay_order_id = $result['order']['order_id'];
    echo $mugglepay_order_id;

} else {
    echo 'Oops!There is a mistake!<br />Error_code{$result[\'error_code\']}<br />Error$result[\'error\']';
}

// Get Order Status - user mugglepay order id
if ($mugglepay_order_id) {
    $binfo = $Mugglepay->query($mugglepay_order_id);
    echo PHP_EOL;
    if ($binfo['order']['status'] == 'PAID') {
        echo "success";
    } else if ($binfo['order']['status']) {
        echo $binfo['order']['status'];
    } else {
        echo "fail";
    }
}

