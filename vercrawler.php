<?php
/**
 * @Component CRON/Joomla Version Crawler
 * @Copyright izonder@gmail.com
 * @Version 2.0
 */

//подключимся к фреймворку Joomla
define( '_JEXEC', 1 );
define( 'JPATH_BASE', realpath(dirname(__FILE__).'/..' )); // путь к корневому каталогу Joomla
define( 'DS', DIRECTORY_SEPARATOR );
/*require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );
$mainframe =& JFactory::getApplication('site');
$mainframe->initialise(); */

//установки по расходу ресурсов
ini_set('max_execution_time', '7200');
ini_set('memory_limit', '2048M');

//дефиниции
define('VERCRAWLER_GLOB_PATTERN', '/home/www/*/public_html');
define('VERCRAWLER_PREG_PATTERN', '#^\/home\/www\/(.*?)\/public_html#si');
//define('VERCRAWLER_GLOB_PATTERN', '/home/*/www');
//define('VERCRAWLER_PREG_PATTERN', '#^\/home\/(.*?)\/www#si');
define('VERCRAWLER_JOOMLA_VER', '/includes/version.php');
define('VERCRAWLER_JOOMLA_VER_15', '/libraries/joomla/version.php');
define('VERCRAWLER_JOOMLA_VER_25', '/joomla.xml');
define('VERCRAWLER_NOTIFY', 'team.dev@flab.ru');

$crawler = new CronVerCrawler;

class CronVerCrawler
{
	private $path = array();
	private $info = array();

	const release = '#\$RELEASE\s*\=\s*[\'"](.*?)[\'"]#si';
	const devlevel = '#\$DEV_LEVEL\s*\=\s*[\'"](.*?)[\'"]#si';
	const xml = '#<version>(.*?)</version>#si';

	function __construct()
	{
		//получим список доменов/путей
		$this->pathList();

		//отпарсим все файлы version.php
		$this->parse();

		//получим актуальную версию
		$this->actual();

		//уведомим админа
		$this->notify();
	}

	private function pathList()
	{
		$this->path = array_merge(glob(VERCRAWLER_GLOB_PATTERN.VERCRAWLER_JOOMLA_VER), glob(VERCRAWLER_GLOB_PATTERN.VERCRAWLER_JOOMLA_VER_15), glob(VERCRAWLER_GLOB_PATTERN.VERCRAWLER_JOOMLA_VER_25)); //print_r($this->path);
	}

	private function parse()
	{
		foreach($this->path as $k=>$v) {
			//RunKit lib
			/*runkit_import($v);
			$this->_tmp[$k] = new JVersion;
			$this->info[$this->_host($v)] = $this->_tmp[$k]->getShortVersion();*/

			$this->info[$this->_host($v)] = $this->_ver($v);
		}
	}

	private function actual()
	{
		$xml = simplexml_load_file('http://update.joomla.org/core/list.xml');
		$this->actual = @$xml->extension['version'];
	}

	private function notify()
	{
		$body = "Actual info about using Joomla version on the server [{$_SERVER['SERVER_ADDR']}]:\n";
		$subject = 'Joomls verCrawler Notify - '.date('d.m.Y');
		foreach($this->info as $k=>$v) $body .= "- Hostname '{$k}' - Joomla ver.#{$v}\n";
		$body .= "Last version - {$this->actual}";
		mail(VERCRAWLER_NOTIFY, $subject, $body);
		echo '<pre>'.$body.'</pre>';
	}

	private function _host($str)
	{
		if(preg_match(VERCRAWLER_PREG_PATTERN, $str, $match)) return $match[1];
		return false;
	}

	private function _ver($path)
	{
		$str = file_get_contents($path);
		if(preg_match(self::release, $str, $r) && preg_match(self::devlevel, $str, $d)) return $r[1].'.'.$d[1];
		else if(preg_match(self::xml, $str, $v)) return $v[1];
		return false;
	}
}