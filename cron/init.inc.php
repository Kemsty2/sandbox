<?php

//подключим конфиг и библиотеки фреймворка
$path = "C:\\webservers\\home\\app\\www\\";
require_once($path."config.php");
require_once($path."_autoload.php");

//инициализация
Registry::getInstance()->getApplication();

//проверка на запуск из командной строки
//if(php_sapi_name() != 'cli') die('Only CLI access allowed!');