<?php
require_once("Core.php");
require_once("Config.php");

$Mugglepay = new Mugglepay($appSecret,$gateWayUrl);

$binfo = $Mugglepay->query($_GET['muggleId']);
if ($binfo['order']['status'] == 'PAID') {
    echo "success";
} else {
    echo "fail";
}

