<?php

require_once('smtp.class.php');

$config['smtp_host']     	= '192.168.108.7';
$config['smtp_auth'] 		= false;
$config['smtp_email'] 		= 'sms@gemotest.ru';
$config['smtp_from']    	= 'Гемотест СМС Информ';

$config['_debug']    		= true;

$mailer = new SMTP($config);
$mailer->send('d.morgachev@gemotest.ru', 'Проверка связи', 'Это простое тестовое сообщение');

var_dump($mailer);
