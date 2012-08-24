<?php

//подключение инициализации
require_once "init.inc.php";
require_once "smpp.class.php";

$smpphost = "193.201.231.44";
$smppport = 2775;
$systemid = "gemotest";
$password = "brosiv93";
$system_type = "WWW";
$from = "Gemotest";

$limit = 100; //не больше $limit СМСок за один раз

$t = 0;
$s = 0;

//забираем очередь
$result = Factory::getDBO()->loadObjectList("SELECT TOP {$limit} * FROM SMSQueue WHERE status = 0 AND attempt < 3 ORDER BY id ASC");
if(is_array($result) && $result) {

	//коннектимся
	$smpp = new SMPPClass();
	$smpp->SetSender($from);
	$smpp->Start($smpphost, $smppport, $systemid, $password, $system_type);
	$smpp->TestLink();

	foreach($result as $v) {
		$r = $smpp->Send($v->recipient_number, $v->message, true);

		++$t;
		if($r === true) {
			++$s;
			$status = 1;
		} else if(($r == 6) || ($r == 13)) { //абонент вне зоны обслуживания или заблокирован
			$status = 0; 
		} else {
			$status = 2;
		}

		Factory::getDBO()->execSafe("UPDATE SMSQueue SET status={$status}, attempt=attempt+1, sent_dt=GETDATE() WHERE id=?", (array)$v->id);
	}

	$smpp->End();
}

echo date("Y-m-d H:i:s") . " - SMPP Sender results: total sent - {$t}, successfully - {$s}\r\n";