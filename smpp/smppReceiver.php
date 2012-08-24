<?php

$start = microtime(true);

$timeout = 300;//50400;
set_time_limit($timeout + 60); //ставим время жизни скрипта чуть больше времени жизни сокета

//подключение инициализации
require_once "init.inc.php";
require_once "smpp.class.php";
require_once "smtp.class.php";

//Настройки SMPP
$smpphost = "193.201.231.44";
$smppport = 2775;
$systemid = "gemotest";
$password = "brosiv93";
$system_type = "WWW";
$from = "Gemotest";

//Настройки SMTP
$config['smtp_host'] = '192.168.108.7';
$config['smtp_auth'] = false;
$config['smtp_email'] = 'sms@gemotest.ru';
$config['smtp_from'] = 'Гемотест СМС Информ';

$t = 0;

//инициализируем SMTP
$mailer = new SMTP($config);

//коннектимся к SMPP
$smpp = new SMPPClass();
$smpp->SetSender($from);

$smpp->_timeout = $timeout;

//echo '<pre>';

while($timeout - (microtime(true) - $start) > 70) {//контролируем время
	if(!$smpp->_socket) {
		$smpp->Start($smpphost, $smppport, $systemid, $password, $system_type);
		$smpp->TestLink();
	}
 
	$sms = $smpp->readSMS();
	if($sms !== false) {
		++$t;
		//$msg = $sms->message; echo chunk_split(bin2hex($msg),2," ");

		$msg = mb_convert_encoding($sms->message, "UTF-8", "UCS-2BE");

		//echo "<h1>{$msg}</h1>"; var_dump($sms); //DEBUG

		$mailer->send("sms@gemotest.ru", "Новое СМС сообщение от ".$sms->source->value, $msg);
		//echo '<br /><br />'.chunk_split(bin2hex($sms->body),2," ");
		//echo '<br /><br />'.chunk_split(bin2hex(mb_convert_encoding('Всем привет!', "UCS-2BE", "UTF-8")),2," ");
	}
}

//$smpp->End();
//echo '</pre>';

echo date("Y-m-d H:i:s") . " - SMPP Receiver results: total received - {$t}\r\n";