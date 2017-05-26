<?php
class TtkMobile_Parser_BdDisk extends TtkMobile_Parser_Abstract
{
	const KEY = 'bdyundisk_key';
	
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * @param string $url
	 * @return boolean | array
	 */
	public  function parse($url)
	{
		$url = Lamb_Utils::authcode($url, self::KEY);
		if ($url) {
			return array('normal' => array("http://baidu.play.yuedisk.com/m3u8.php/2349731749/{$url}"));
		}
		
		return false;
	}		
	
	public static function getUrlFromPlayUrl($url)
	{
		$bit = parse_url($url);
		if (!isset($bit['fragment'])) {
			return '';
		}
		
		parse_str($bit['fragment'], $bit);
		$path = preg_replace('/^\/apps\/y56\//is', '', $bit['video/path']);
		$newpath = array();
		foreach (explode('/', $path) as $item) {
			$newpath[] = urlencode($item);
		}
		$newpath = implode('/', $newpath);		
		return Lamb_Utils::authcode($newpath, self::KEY, 'ENCODE');	
	}
}