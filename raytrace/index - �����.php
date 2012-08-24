<?php

define('DS', DIRECTORY_SEPARATOR);

$v = array(
	array('x' => 1, 'y' => 1),
	array('x' => 10, 'y' => 2),
	array('x' => 15, 'y' => 7),
	array('x' => 12, 'y' => 16),
	array('x' => 7, 'y' => 14),
	array('x' => 3, 'y' => 5)
);

$p = array('x' => 8, 'y' => 1);
$f = pi()/20; //берем шаговый угол 1/10*PI rad ~ 9 grad

$c = count($v);
$intersect = 0;

$grid = array(
	array(array('x1' => 0, 'y1' => 4, 'x2' => 20, 'y2' => 4, 'chain' => 4)),
	array(array('x1' => 0, 'y1' => 8, 'x2' => 20, 'y2' => 8, 'chain' => 8)),
	array(array('x1' => 0, 'y1' => 12, 'x2' => 20, 'y2' => 12, 'chain' => 12)),
	array(array('x1' => 0, 'y1' => 16, 'x2' => 20, 'y2' => 16, 'chain' => 16)),
	array(array('x1' => 4, 'y1' => 0, 'x2' => 4, 'y2' => 20, 'chain' => 4)),
	array(array('x1' => 8, 'y1' => 0, 'x2' => 8, 'y2' => 20, 'chain' => 8)),
	array(array('x1' => 12, 'y1' => 0, 'x2' => 12, 'y2' => 20, 'chain' => 12)),
	array(array('x1' => 16, 'y1' => 0, 'x2' => 16, 'y2' => 20, 'chain' => 16))
);
$img = new image($p['x'], $p['y'], $grid);

for($i = 0; $i < $c; $i++) {
	$j = (($i + 1) == $c)?0:($i+1); //следующая вершина, или нулевая

	$K = ($v[$j]['y'] - $v[$i]['y'])/($v[$j]['x'] - $v[$i]['x']);
	$img->vector($img->scaleX($v[$i]['x']), $img->scaleY($v[$i]['y']), $img->scaleX($v[$j]['x']), $img->scaleY($v[$j]['y']), imagecolorallocate($img->img, 0, 0, 0));

	//создадим лучи
	for($s = 1; $s < 10; $s++) { //делаем 9 итераций (при 10 итерации угол будет равен 90 градусов, а тангенс соответсвенно неопределен)
		$k = tan($f * $s); //echo $k . ' - ' .$K . '<br />';

		$x = (($v[$i]['y'] - $p['y']) - ($K * $v[$i]['x'] - $k * $p['x']))/($k - $K);
		$y = $K*($x - $v[$i]['x']) + $v[$i]['y'];

		//TODO проверка на попадание в интервал по X и по Y
		if($k != $K) { //исключаем ситуации совпадения векторов и персечения с вершинами
			//$img->vector($img->scaleX($p['x']), $img->scaleY($p['y']), $img->scaleX($x), $img->scaleY($y));
			$matchX = ((($x < $v[$i]['x']) && ($x > $v[$j]['x'])) || (($x < $v[$j]['x']) && ($x > $v[$i]['x']))) && ($x > $p['x']); //т.к. рассматриваются лучи, а не прямые, введем огранияения только для пересечений в интервале (0; pi/2) относительно точки
			$matchY = ((($y < $v[$i]['y']) && ($y > $v[$j]['y'])) || (($y < $v[$j]['y']) && ($y > $v[$i]['y']))) && ($y > $p['y']);
			if($matchX && $matchY) {
				++$intersect; 
				$img->vector($img->scaleX($p['x']), $img->scaleY($p['y']), $img->scaleX($x), $img->scaleY($y));
				$img->num($img->scaleX($x), $img->scaleY($y), "({$x}; {$y})");
			}
		}
	}
}

//проверяем на четность
echo '<h1>Пересечений = ' . $intersect . ': точка лежит ' . ((($intersect % 2) != 0)?'внутри':'снаружи').'</h1>';

class image
{
	private $w = 600;
	private $h = 600;

	private $scaleX = 0;
	private $scaleY = 0;

	public $img = null;
	private $name = 'map.png';

	function __construct($x, $y, $chains)
	{
		$this->scaleX = ($this->w-20)/(20-0);
		$this->scaleY = ($this->h-20)/(20-0);

		$this->img = imagecreatetruecolor($this->w, $this->h);

		$white = imagecolorallocate($this->img, 255, 255, 255);
		$red = imagecolorallocate($this->img, 255, 0, 0);
		$green = imagecolorallocate($this->img, 0, 222, 0);
		$blue = imagecolorallocate($this->img, 0, 0, 255);
		$black = imagecolorallocate($this->img, 0, 0, 0);

		imagefill($this->img, 0, 0, $white); //фон
		imagerectangle($this->img, 0, 0, $this->w-1, $this->h-1, $black);

		$this->point($this->scaleX($x), $this->scaleY($y), $red); //поставим точку
		if(count($chains) > 0) {
			foreach($chains as $chain) {
				foreach($chain as $v) {
					$this->vector($this->scaleX($v['x1']), $this->scaleY($v['y1']), $this->scaleX($v['x2']), $this->scaleY($v['y2']), $blue);
				}
				$this->num($this->scaleX($v['x2']), $this->scaleY($v['y2']), $v['chain'], $green);
			}
		}
	}

	function __destruct()
	{
		@unlink(dirname(__FILE__).DS.$this->name);
		imagepng($this->img, dirname(__FILE__).DS.$this->name);
		imagedestroy($this->img);
		echo '<img src="'.$this->name.'" />';
	}

	function point($x, $y, $color)
	{
		imageellipse($this->img, $x, $y, 4, 4, $color);
		imagefill($this->img, $x, $y, $color);
	}

	function vector($x1, $y1, $x2, $y2, $color=null)
	{
		//echo "{({$x1}, {$y1}); ({$x2}, {$y2})}<br />";
		if(is_null($color)) $color = imagecolorallocate($this->img, 0xF8, 0x65, 0x18);
		imageline($this->img, $x1, $y1, $x2, $y2, $color);
	}

	function num($x, $y, $num, $color=null)
	{
		$font = dirname(__FILE__).DS.'arial.ttf';
		if(is_null($color)) $color = imagecolorallocate($this->img, 0, 0, 0);
		imagettftext($this->img, 10, 0, $x+3, $y-3, $color, $font, $num);
	}

	function scaleX($coord)
	{
		return ($coord - 0)*$this->scaleX;
	}

	function scaleY($coord)
	{
		return $this->h -(($coord - 0)*$this->scaleY);
	}
}
 