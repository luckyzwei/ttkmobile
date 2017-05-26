<?php
class Ttk_Parser_Bilibili extends Ttk_Parser_Abstract
{
	protected $appkey = '85eb6835b0a1034e'; //95acd7f6cc3392f3
	
	protected $appsecret = '2ad42749773c441109bdc0191257a664';
	
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * @param string $url
	 * @return boolean | array
	 */
	public  function parse_bakup($url)
	{
		if (!preg_match('/av(\d+)/is', $url, $result)) {
			return false;
		}
		$vid = $result[1];
		
		$page = 1;
		if (preg_match('/page\=(\d+)/is', $url, $result) || preg_match('/index\_(\d+)\./is', $url, $result)) {
			$page = $result[1];
		}
		
		$url = "http://www.bilibili.com/m/html5?aid={$vid}&page={$page}";	
		$html = Lamb_Http::quickGet($url, 5, false, $status);
		
		if (!$html || $status != 200) {
			return false;
		}
		
		try {
			$html = json_decode($html, true);
		} catch (Exception $e) {
			return false;
		}
		
		if (isset($html['src'])) {
			return array('normal' => array($html['src']));
		}
		
		return false;
	}		
	
	public function parse($url)
	{
		if (!preg_match('/av(\d+)/is', $url, $result)) {
			return false;
		}
		$vid = $result[1];
		
		$page = 1;
		if (preg_match('/page\=(\d+)/is', $url, $result) || preg_match('/index\_(\d+)\./is', $url, $result)) {
			$page = $result[1];
		}
		
		$param = $this->getSign(array(
			'type' => 'json',
			'id' => $vid,
			'page' => $page
		));	
		$time = time() + 86400;
		$cookie = "DedeUserID=1856502; SESSDATA=b121134c%2C1449476191%2C0f4eee10";

		$url = "http://api.bilibili.com/view?" . $param;
		$html = Lamb_Http::request(array(
			'url' => $url,
			'headers' => array(
				'user_agent' => 'Mozilla/5.0 (Linux; Android 4.3; Nexus 7 Build/JSS15Q) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2307.2 Safari/537.36',
			'Cookie' => "DedeUserID=1856502; SESSDATA=b121134c%2C1449640659%2C738446e0;"
			)
		), $status);
		
		//Lamb_Debuger::debug($html);
		if (!$html || $status != 200) {
			return false;
		}

		if (!preg_match('/"cid"\:(\d+)/is', $html, $result)) {
			return false;
		}
		
		$url = "http://interface.bilibili.com/playurl?otype=json&cid={$result[1]}&type=flv&quality=4&appkey={$this->appkey}";	
		$html = Lamb_Http::quickGet($url, 5, false, $status);

		if (!$html || $status != 200) {
			return false;
		}	
		
		try {
			$data = json_decode($html, true);
		} catch (Exception $e) {
			return false;
		}

		if (!isset($data['durl']) || !isset($data['durl'][0]) || !isset($data['durl'][0]['url'])) {
			return false;
		}
		
		$urls = array();
		foreach ($data['durl'] as $item) {
			$urls[] = $item['url'];
		}
		
		return array('normal' => $urls);
	}
	
	public function getSign(array $param)
	{
		$param['appkey'] = $this->appkey;
		ksort($param);
		$new_params = array();
		
		foreach ($param as $key => $val) {
			$new_params[] = "{$key}={$val}";
		}
		
		$new_params = implode('&', $new_params);
		$sign = md5($new_params . $this->appsecret);
	
		return "{$new_params}&sign={$sign}";
	}
}