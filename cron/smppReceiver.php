<?php

echo date("Y-m-d H:i:s") . " - Starting program\r\n"; flush(); //сбросим вывод

$start = microtime(true);

$timeout = 50400;
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

$smpp->_timeout = ($timeout > 3600)?3600:$timeout; //ставим таймаут сокета не больше часа

//echo '<pre>';

while(($timeout - (microtime(true) - $start)) > 1800) {//контролируем время
	echo date("Y-m-d H:i:s") . " - New iterate starting\r\n"; flush(); //сбросим вывод
	while(!$smpp->_socket) {
		echo date("Y-m-d H:i:s") . " - Socket is flushed. Trying to connect\r\n"; flush(); //сбросим вывод
		$smpp->Start($smpphost, $smppport, $systemid, $password, $system_type);
		$smpp->TestLink();
	}

	echo date("Y-m-d H:i:s") . " - Socket is OK\r\n"; flush(); //сбросим вывод
 
	$sms = $smpp->readSMS();
	if(($sms !== false)){
		++$t;
		//$msg = $sms->message; echo chunk_split(bin2hex($msg),2," ");

		$msg = ($sms->dataCoding == SMPP::DATA_CODING_UCS2)?mb_convert_encoding($sms->message, "UTF-8", "UCS-2BE"):$sms->message;

		//echo "<h1>{$msg}</h1>"; var_dump($sms); //DEBUG

		if(! ($sms->esmClass & SMPP::ESM_DELIVER_SMSC_RECEIPT)) $mailer->send("sms@gemotest.ru", "Новое СМС сообщение от ".$sms->source->value, $msg); //не пересылаем смс
		echo date("Y-m-d H:i:s") . " - New SMS received [ESM {$sms->esmClass}][RCPT {$sms->source->value}]: {$msg}\r\n"; flush(); //сбросим вывод
		//echo '<br /><br />'.chunk_split(bin2hex($sms->body),2," ");
		//echo '<br /><br />'.chunk_split(bin2hex(mb_convert_encoding('Всем привет!', "UCS-2BE", "UTF-8")),2," ");
	} else {
		echo date("Y-m-d H:i:s") . " - SMS reading return false\r\n"; flush(); //сбросим вывод
		$smpp->End();
	}
	echo date("Y-m-d H:i:s") . " - New iterate ending\r\n"; flush(); //сбросим вывод
}

//$smpp->End();
//echo '</pre>';

echo date("Y-m-d H:i:s") . " - SMPP Receiver results: total received - {$t}\r\n";