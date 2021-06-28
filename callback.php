<?php
require_once("Core.php");
require_once("Config.php");

$Mugglepay = new Mugglepay($appSecret,$gateWayUrl);

$inputString = file_get_contents('php://input', 'r');
$inputStripped = str_replace(array("\r", "\n", "\t", "\v"), '', $inputString);
$inputJSON = json_decode($inputStripped, true);

$data = array();
if ($inputJSON !== null) {
    $data['status'] = $inputJSON['status'];
    $data['order_id'] = $inputJSON['order_id'];
    $data['merchant_order_id'] = $inputJSON['merchant_order_id'];
    $data['price_amount'] = $inputJSON['price_amount'];
    $data['price_currency'] = $inputJSON['price_currency'];
    $data['created_at_t'] = $inputJSON['created_at_t'];
}

// 准备待签名数据
$str_to_sign = $Mugglepay->prepareSignId($inputJSON['merchant_order_id']);
$resultVerify = $Mugglepay->verify($str_to_sign, $inputJSON['token']);
$isPaid = $data !== null && $data['status'] !== null && $data['status'] === 'PAID';

if ($resultVerify && $isPaid) {
    echo "success";
} else {
    echo "fail";
}
