<?php
class Ttk_Parser_Qianmo extends Ttk_Parser_Abstract
{
	
	public function __construct()
	{
		parent::__construct();
	}


	public function parse($url)
	{
		$html = Lamb_Http::quickGet('http://1.ttkqm.sinaapp.com/qianmo.php?url=' . urlencode($url), 5, false, $status);
		if (!$html) {
			return false;
		}
		
		return array('normal' => array($html));	
	/*
		$html = Lamb_Http::quickGet($url, 5, false, $status);
		
		if (!$html || $status != 200) {
			return false;
		}
		
		if (!preg_match('/"video\_id"\:"(.*?)"/is', $html, $result)) {
			return false;
		}
		
		$html = Lamb_Http::quickGet("http://qianmo.com/api/v/{$result[1]}", 5, false, $status);
		
		if (!$html || $status != 200) {
			return false;
		}
		
		try {
			$data = json_decode($html, true);
		} catch (Exception $e) {
			return false;
		}
		
		if ($data['status'] != 200 || !isset($data['seg'])) {
			return false;
		}
		$key = key($data['seg']);
		$url = '';
		foreach ($data['seg'][$key][0]['url'] as $_url) {
			if (strpos($_url[0], 'http://wscdn') !== false) {
				$url = $_url[0];
				break;
			}
		}
		
		if (!$url) {
			$url = 	$data['seg'][$key][0]['url'][0][0];	
		}
		
		return array('normal' => array($url));	*/
		
		$url_refer = "http://agent.play.yuedisk.com/play.php?uid=92660114&amp;url={$url}";
		$url_refer = str_replace('&amp;', '&', $url_refer);
		$url = 'http://www.dyued.com/movie/2015/10/78.html';
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