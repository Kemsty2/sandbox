<?php

//подключение инициализации
require_once "init.inc.php";
require_once "smtp.class.php";
set_time_limit(1800);

//Настройки SMTP
$config['smtp_host'] = '192.168.108.7';
$config['smtp_auth'] = false;
$config['smtp_email'] = 'sms@gemotest.ru';
$config['smtp_from'] = 'Гемотест СМС Информ';

//инициализируем SMTP
$mailer = new SMTP($config);

//сформируем маппинг медсестер
$sql = "SELECT u.id AS ARRAY_KEY, u.name, n.nurse_email AS email, n.nurse_phone AS phone 
	FROM #__nurses n
	LEFT JOIN #__users u ON u.id = n.user_id";

$map = Factory::getDBO()->select($sql);

$sql = "SET NOCOUNT ON;

	-- выгрузим маршруты
	UPDATE #__orders
	SET [status] = 2
	WHERE [status] = 1 
	--AND called = 1
	AND nurse_id != 0
	AND visit_date = CONVERT(date, DATEADD(d,1,GETDATE()));

	-- заказы, которые не соответствуют требованиям сбросим в статус неподтвержденных
	UPDATE #__orders
	SET [status] = 0
	WHERE [status] = 1;

	SELECT DISTINCT nurse_id as id
	FROM #__orders
	WHERE [status] = 2;

	SET NOCOUNT OFF;";

$date = date('d.m.Y', strtotime("+1 day"));
$tpl = "Ув. %s. Ваш маршрутный лист на {$date} укомлектован. Адрес в Интернете: http://office.gemotest.ru/erp/";

$c = 0;
$ids = array();
foreach(Factory::getDBO()->queryObj($sql) as $v) {
	$sql = "INSERT INTO SMSQueue (recipient_number, message, queue_dt)
		VALUES (:phone, :msg, GETDATE());";

	//валидаторы
	$map[$v->id]['name'] = trim($map[$v->id]['name']);
	$map[$v->id]['phone'] = preg_replace('#[^0-9]#si', '', $map[$v->id]['phone']);

	//приведем телефон к правильному виду: 79xxxxxxxxx
	if(strlen($map[$v->id]['phone']) > 11) {
		//ничего не делаем, ставим как есть, вдруг это зарубежный номер
	} else if(strlen($map[$v->id]['phone']) == 11) {
		//проверим начальную цифру, если 8, заменим на 7
		if($map[$v->id]['phone']{0} == 8) $map[$v->id]['phone']{0} = 7;
	} else if(strlen($map[$v->id]['phone']) == 10) {
		//номер вида 92xxxxxxxx добавим 7 в самом начале
		$map[$v->id]['phone'] = '7' . $map[$v->id]['phone'];
	} else {
		//номер меньше 10 цифр, а значит некорректный - скипуем
		$map[$v->id]['phone'] = null;
	}

	$msg = sprintf($tpl, $map[$v->id]['name']);
	$params = array(':phone' => $map[$v->id]['phone'], ':msg' => $msg);

	if(!empty($map[$v->id]['email'])) $mailer->send($map[$v->id]['email'], "Маршрут на ".$date, $msg);
	if(!empty($map[$v->id]['phone'])) Factory::getDBO()->execSafe($sql, $params);
	++$c;
}

echo date("Y-m-d H:i:s") . " - Nurse notified results: total notified - {$c}\r\n";