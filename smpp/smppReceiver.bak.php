<?php

//подключение инициализации
require_once "init.inc.php";

$GLOBALS['SMPP_ROOT'] = dirname(__FILE__).DS.'smpp';
require_once $GLOBALS['SMPP_ROOT'].DS.'protocol'.DS.'smppclient.class.php';
require_once $GLOBALS['SMPP_ROOT'].DS.'transport'.DS.'tsocket.class.php';

$smpphost = "193.201.231.44";
$smppport = 2775;
$systemid = "gemotest";
$password = "brosiv93";

$t = 0;

// Коннектимся
$transport = new TSocket($smpphost, $smppport);
$transport->setRecvTimeout(60000); // ждем данных 60 секунд
$smpp = new SmppClient($transport);

$smpp->debug = true;

$transport->open();
$smpp->bindReceiver($systemid, $password);

// Читаем
$sms = $smpp->readSMS();
echo "SMS:\n";
var_dump($sms);

$smpp->close();

echo date("Y-m-d H:i:s") . " - SMPP Receiver results: total receive - {$t}\r\n";