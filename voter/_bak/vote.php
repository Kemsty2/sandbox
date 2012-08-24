<?php
session_start();
define('VF', 'vote.data');
//ответы
$message = array(
	0	=> 	'Ебать ты жлоб! Чтоб тебе твои дети так помогали!..',
	1	=>	'Зарядились оптимизмом, сосем дальше...',
	2	=>	'Ставлю сиськи Анны Семенович на то, что завтра не будет',
	3	=>	'Охуенная перспектива. Иди на хер, а то накаркаешь...',
	4	=>	'Пиздишь и не краснеешь. Лучше бы что-то подкинул',
	5	=>	'Ты ебанулся с такими ставками?.. А вобщем-то, знаешь, оставь себе мою зарплату, мне уже и так неплохо...'
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
	'total' => number_format(($data[0]*10+$data[1]*1000+$data[3]*500)/30 + $data[2]*1000 + $data[5]*1000000, 2, '.', ' '),
	'qty' => array_sum($data),
	'zhlob' => $data[0]+$data[4],
	'votes' => $_SESSION['vote']
);
echo json_encode($response);