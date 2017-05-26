<?php
class Ttk_Parser_Dyued extends Ttk_Parser_Abstract
{
	
	public function __construct()
	{
		parent::__construct();
	}


	public function parse($url)
	{
		$html = Lamb_Http::quickGet($url, 5, false, $status);
		
		if (!$html || $status != 200) {
			return false;
		}
		
		if (!preg_match('/<iframe\s*src\="(.*?)"/is', $html, $result)) {
			return false;
		}
		
		$url_refer = $result[1];
		$url_refer = str_replace('&amp;', '&', $url_refer);
		$bits = parse_url($url_refer);
		$host = $bits['host'];
		$port = isset($bits['port']) ? $bits['port'] : 80;
		$path = isset($bits['path']) ? $bits['path'] : '/';
		if (isset($bits['query'])) {
			$path .= '?'.$bits['query'];
		}		
		
		$client = new Lamb_Http($host, $port);
		$client->max_redirects = 0;
		$client->get($path);
		$status = $client->getStatus();
		
		if ($status == 200) {
			$html = $client->getContent();
			if (!preg_match('/a\:\s*\'(.*?)\'/is', $html, $result)) {
				return false;
			}	
			
			$url = "http://ydisks.duapp.com{$result[1]}";
			
			$bits = parse_url($url);
			$host = $bits['host'];
			$port = isset($bits['port']) ? $bits['port'] : 80;
			$path = isset($bits['path']) ? $bits['path'] : '/';
			if (isset($bits['query'])) {
				$path .= '?'.$bits['query'];
			}		
			
			$client = new Lamb_Http($host, $port);
			$client->customHeaders = array('Referer' => $url_refer);
			$client->max_redirects = 0;
			$client->get($path);
			$headers = $client->getHeaders();
			
			$url = @$headers['location'];						
		} else if ($status == 302) {
			$headers = $client->getHeaders();
			$_url = @$headers['location'];
			
			$html = Lamb_Http::request(array(
				'url' => $_url,
				'headers' => array(
					'Referer' => $url
				)
			), $status);	
			
			if (!preg_match('/f\:\s*\'(.*?)\'/is', $html, $result)) {
				return false;
			}			
			
			if (strpos($result['1'], '/ckplayer/m3u8.swf') !== false) {
				if (!preg_match('/<video\s*src\="(.*?)"/is', $html, $result)) {
					return false;
				}
				return array('normal' => array("http://ydisks.duapp.com{$result[1]}"));
			}
			
			$url = "http://ydisks.duapp.com{$result[1]}";
			$html = Lamb_Http::request(array(
				'url' => $url,
				'headers' => array(
					'Referer' => $url,
					'Cookie' => 'ydisk_agent=ac7ead69ywutrQWwEEAlEEBlJLClAIBwYOV1dbGxAbUFVXDQNQBVwHBUtBTQ;'
				)
			), $status);

			if (!$html || $status != 200) {
				return false;
			}	
			
			if (!preg_match('/<file><\!\[CDATA\[(.*?)\]\]>/is', $html, $result))	{
				return false;
			}
			
			$url = $result[1];
		}
			
		return array('normal' => array($url));		
	}
}