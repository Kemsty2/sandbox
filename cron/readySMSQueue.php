<?php

//подключение инициализации
require_once "init.inc.php";
set_time_limit(1800);

$sql = "SELECT o.OrderIDForCACHE as id, p.LastName+' '+p.Name as Name, p.MobileNumber
	FROM OrdersToExport o WITH(NOLOCK)
	LEFT JOIN Patients p WITH(NOLOCK) ON o.PatID = p.AID
	WHERE o.[Status] = 2
	--AND o.DateReg > '2012/04/01'
	AND [dbo].[OrderPercentCompl](isnull(o.CacheOrderID,'')) = '100'
	AND o.notifyQueued = 0
	AND p.MobileNotify = 1";

$tpl = "Ув. %s. Ваш заказ №%s полностью выполнен. Лаборатория «Гемотест» :)";

$c = 0;
$ids = array();
foreach(Factory::getDBO()->queryObj($sql) as $v) {
	$sql = "INSERT INTO SMSQueue (recipient_number, message, queue_dt)
		VALUES (:phone, :msg, GETDATE());";

	$v->Name = trim($v->Name);
	$v->MobileNumber = preg_replace('#[^0-9]#si', '', $v->MobileNumber);
	if(empty($v->MobileNumber)) continue;

	//приведем телефон к правильному виду: 79xxxxxxxxx
	if(strlen($v->MobileNumber) > 11) {
		//ничего не делаем, ставим как есть, вдруг это зарубежный номер
	} else if(strlen($v->MobileNumber) == 11) {
		//проверим начальную цифру, если 8, заменим на 7
		if($v->MobileNumber{0} == 8) $v->MobileNumber{0} = 7;
	} else if(strlen($v->MobileNumber) == 10) {
		//номер вида 92xxxxxxxx добавим 7 в самом начале
		$v->MobileNumber = '7' . $v->MobileNumber;
	} else {
		//номер меньше 10 цифр, а значит некорректный - скипуем
		continue;
	}

	$params = array(':phone' => $v->MobileNumber, ':msg' => sprintf($tpl, $v->Name?$v->Name:'Клиент', $v->id));

	if(Factory::getDBO()->execSafe($sql, $params)) {
		++$c;
		$ids[] = $v->id;
	}
}

$sql = "UPDATE OrdersToExport SET notifyQueued = 1 WHERE OrderIDForCACHE IN (".implode(',', $ids).")"; //теперь обновим таблицу 1 раз
Factory::getDBO()->execSafe($sql);

echo date("Y-m-d H:i:s") . " - Orders Ready SMS Queue results: total queued - {$c}\r\n";