<?php
session_start();
define('VF', 'vote.data');
//ответы
$message = array(
	0	=> 	'- Уж мы их душили, душили... &copy; Собачье сердце',
	1	=>	'- Все! Кина не будет. Электричество кончилось. &copy; Джетльмены удачи',
	2	=>	'- Вы знаете, кто этот мощный старик? Это гигант мысли, отец русской демократии и особа, приближённая к Императору! <br />- Скажите... А двести рублей не могут спасти гиганта мысли?<br />&copy; 12 стульев',
	3	=>	'- Помните я говорила в прошлом году что жизнь говно?? Ну так это был еще марципанчик. &copy; Ф.Раневская',
	4	=>	'- Вы все говно! &copy; Самый лучший фильм',
	5	=>	'- А у вас есть то же самое, только с перламутровыми пуговицами? &copy; Бриллиантовая рука'
);

$results = array(
	0	=>	0,
	1	=>	0,
	2	=>	0,
	3	=>	0,
	4	=>	0,
	5	=>	0
);
$storage = json_decode(file_get_contents(VF), true);
$data = $storage?$storage:$results;

//проверим голос
$v = (int)isset($_POST['v'])?$_POST['v']:false;

//голосуем
if($v !== false) {
	$v = $v % 6;
	$_SESSION['vote'] = intval($_SESSION['vote']) + 1;
	$data[$v]++;
	file_put_contents(VF, json_encode($data));
}

//подготовим данные
$chart = array();
//foreach($data as $k=>$d) $chart[] = array($k, $d);
$chart = $data;

$response = array(
	'message' => ($v !== false)?$message[$v]:'', 
	'chart' => array($chart), 
	'total' => number_format($data[0] + $data[1]*1000 + $data[2]*5000 + $data[3]*500, 2, '.', ' '),
	'qty' => array_sum($data),
	'zhlob' => $data[0]+$data[4]+$data[5],
	'votes' => $_SESSION['vote']
);
echo json_encode($response);