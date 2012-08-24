<?php

//подключим конфиг и библиотеки фреймворка
$path = "Z:\\home\\app\\www\\";
require_once($path."config.php");
require_once($path."_autoload.php");

//инициализация
//Registry::getInstance()->getApplication();

//проверка на запуск из командной строки
//if(!strstr(php_sapi_name(), 'cli')) die('Only CLI access allowed!');