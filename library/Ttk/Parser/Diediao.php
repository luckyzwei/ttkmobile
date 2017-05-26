<?php
class Ttk_Parser_Diediao extends Ttk_Parser_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function parse($url)
	{
		$urls = explode('|', $url);
		$playurl = $urls[0];
		$playname = $urls[1];
		
		if ($playname == 'url') {
			$ret = $this->parse_byurl($url);
			if (!$ret) {
				return false;
			}
			$urls = explode('|', $ret);
			$playurl = $urls[0];
			$playname = $urls[1];			
		}
		
		if ($playname == 'flv') {
			$url = "http://ck.diediao.com:8899/api23/skey/{$playurl}_nly/format/dm3u8/";
			$bits = parse_url($url);
			$host = $bits['host'];
			$port = isset($bits['port']) ? $bits['port'] : 80;
			$path = isset($bits['path']) ? $bits['path'] : '/';
			if (isset($bits['query'])) {
				$path .= '?'.$bits['query'];
			}	
						
			$client = new Lamb_Http($host, $port);
			$client->max_redirects = 0;
			$client->get($path);
			$headers = $client->getHeaders();	
			
			if ($client->getStatus() == 302 && isset($headers['location'])) {
				return array('normal' => array($headers['location']));
			} else {
				return false;
			}	
		} else {
			$url = 'http://app.diediao.com/5201/index.php?ckid=' . urlencode($playurl);
			$html = Lamb_Http::quickGet($url, 5, false, $status);
			
			if (!$html || $status != 200) {
				return false;
			}

			if (!preg_match('/<file><\!\[CDATA\[(.*?)\]\]>/is', $html, $result))	{
				return false;
			}
			
			return array('normal' => array($result[1]));				
		}
		
		return false;
	}
	
	public static function parse_byurl($url)
	{
		if (!preg_match('/player\-(\d+)\-(\d+)\./is', $url, $result)) {
			return FALSE;
		}
		$index = $result[1];
		$subindex = $result[2];
		
		$html = Lamb_Http::quickGet($url, 5, false, $status);
		
		if (!$html || $status != 200) {
			return FALSE;	
		}
		
		if (!preg_match('/<div\s*id\="players">.*?src\="(.*?)"/is', $html, $result)) {
			return FALSE;
		}
		
		$url = "http://www.diediao.com{$result[1]}";
		$html = Lamb_Http::quickGet($url, 5, false, $status);
		
		if (!$html || $status != 200) {
			return FALSE;	
		}
		
		if (!preg_match('/ff\_urls\=\'(.*?)\';/is', $html, $result)) {
			return FALSE;
		}		
		
		try {
			$urls = json_decode($result[1], true);
		} catch (Exception $e) {
			return false;
		}
		
		if (!isset($urls['Data']) || !isset($urls['Data'][$index]) || !isset($urls['Data'][$index]['playurls']) || !isset($urls['Data'][$index]['playurls'][$subindex])) {
			return false;
		}
		
		return $urls['Data'][$index]['playurls'][$subindex][1] . '|' . $urls['Data'][$index]['playname'];
	}
}