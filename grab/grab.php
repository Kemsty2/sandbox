<?php
//константы
define('URL', 'http://wbpreview.com/previews/WB0C4JJ9R/index.html');
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__).DS.'site'.DS);

//стартуем
$grab = new Grabber(URL);
$grab->grab();

//граббер
class Grabber
{
	private $queue = array();
	private $root = null;

	function __construct($url = null)
	{
		$this->set($url);
	}

	function set($url)
	{
		if($url) $this->queue[] = $url;
	}

	function grab()
	{
		if($this->queue) {
			while(true) {
				if($this->queue) $u = array_pop($this->queue);
				else break; 

				$url = parse_url($u);
				$data = file_get_contents($u);
				if(!$this->root) {
					$this->root = $url;
					file_put_contents(ROOT.'index.html', $data);
				} else {
					preg_match('#\/([^/]+)$#si', $u, $match); print_r($match);
					file_put_contents(ROOT.$match[1], $data);
				}
				if(!preg_match('#\.(jpg|jpeg|png|gif|js)#si', $u)) $this->_parse($data);
			}
		}
	}

	function _parse($data)
	{
		preg_match_all('#(href|src)=[\'"](.*?)[\'"]#si', $data, $matches);
		foreach($matches[2] as $u) {
			if($u != '#' && !preg_match('#javascript:#si', $u)) {
				$url = parse_url($u);
				if(isset($url['host']))	$u = 'http://'.$url['host'].$url['path'];
				else $u = $this->root['scheme'].'://'.$this->root['host'].dirname($this->root['path']).'/'.$url['path'];
				$u = str_replace('/./', '/', $u);
				$this->queue[] = $u;
				echo $u.'<br />';
			}
		}
	}
}