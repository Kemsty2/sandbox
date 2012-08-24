<?php

//подключение инициализации
require_once "init.inc.php";
require_once "smpp.class.php";

$smpphost = "193.201.231.44";
$smppport = 2775;
$systemid = "gemotest";
$password = "brosiv93";
$system_type = "WWW";
$from = "Gemotest";//"79254247225";

$limit = 100; //не больше $limit СМСок за один раз

$t = 0;
$s = 0;

//забираем очередь
if(($result = Factory::getDBO()->loadObjectList("SELECT TOP {$limit} * FROM SMSQueue WHERE status = 0 AND attempt < 3 ORDER BY id ASC")) !== false) {

	//коннектимся
	$smpp = new SMPPClass();
	$smpp->SetSender($from);
	$smpp->Start($smpphost, $smppport, $systemid, $password, $system_type);
	$smpp->TestLink(); var_dump($smpp->Send('79261217931', 'Ув. Клиент. Ваш заказ №91523796 полностью выполнен. Лаборатория «Гемотест» :)', true));

	/*foreach($result as $v) {
		$r = $smpp->Send($v->recipient_number, $v->message, true);

		++$t;
		if($r) ++$s;

		Factory::getDBO()->execSafe("UPDATE SMSQueue SET status=".intval($r).", attempt=attempt+1, sent_dt=GETDATE() WHERE id=?", (array)$v->id);
	}*/

	$smpp->End();
}

echo date("Y-m-d H:i:s") . " - SMPP Sender results: total sent - {$t}, successfully - {$s}\r\n";