<?php

//подключение инициализации
require_once "init.inc.php";

$sql = "SELECT o.OrderIDForCACHE as id, p.LastName+' '+p.Name as Name, p.MobileNumber
	FROM OrdersToExport o
	LEFT JOIN Patients p ON o.PatID = p.AID
	WHERE o.[Status] = 2
	--AND o.DateReg > '2012/04/01'
	AND [dbo].[OrderPercentCompl](isnull(o.CacheOrderID,'')) = '100'
	AND o.notifyQueued = 0
	AND p.MobileNotify = 1";

$tpl = "Ув. %s. Ваш заказ №%s полностью выполнен. Лаборатория «Гемотест» :)";

$c = 0;
foreach(Factory::getDBO()->queryObj($sql) as $v) {
	$sql = "INSERT INTO SMSQueue (recipient_number, message, queue_dt)
		VALUES (:phone, :msg, GETDATE());

		UPDATE OrdersToExport SET notifyQueued = 1 WHERE OrderIDForCACHE = :id;";

	$v->Name = trim($v->Name);
	$v->MobileNumber = preg_replace('#[^0-9]#si', '', $v->MobileNumber);
	if(empty($v->MobileNumber)) continue;

	$params = array(':phone' => $v->MobileNumber, ':msg' => sprintf($tpl, $v->Name?$v->Name:'Клиент', $v->id), ':id' => $v->id);

	if(Factory::getDBO()->execSafe($sql, $params)) ++$c;
}

echo date("Y-m-d H:i:s") . " - Orders Ready SMS Queue results: total queued - {$c}\r\n";