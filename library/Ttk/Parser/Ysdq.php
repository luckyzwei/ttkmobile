<?php
/**
 * 采集影视大全www.yingshidaquan.cc
 */
class Ttk_Parser_Ysdq extends Ttk_Parser_Abstract
{
	
	public function __construct()
	{
		parent::__construct();
	}


	public function parse_byurl($url, $isRet = false)
	{
		if (!preg_match('/\/play\/.*?\-(\d+)\-(\d+)/is', $url, $result)) {
			return false;
		}
		$index = $result[1];
		$subindex = $result[2] - 1;
		
		$html = Lamb_Utils::fetchContentByUrlC($url);

		if (!preg_match('/ff_urls=\'(.*?)\';/is', $html, $result)) {
			return false;
		}
		$ff_urls = $result[1];
		try {
			$ff_urls = json_decode($ff_urls, true);
		} catch (Exception $e) {
			return false;
		}

		if (!isset($ff_urls['Data']) || !isset($ff_urls['Data'][$index])) {
			return false;
		}
		$play_info = $ff_urls['Data'][$index];

		if (!isset($play_info['playurls']) || !isset($play_info['playurls'][$subindex])) {
			return false;
		}
		$vid = $play_info['playurls'][$subindex][1];
		
		if ($isRet) {
			return array('vid' => $vid, 'playname' => $play_info['playname']);
		}
		return "{$vid}|{$play_info['playname']}";
		//return $this->parse("{$vid}|{$play_info['playname']}");
	}
	
	/**
	 * 采集影视大全
	 * url地址格式 $vid|$playname
	 *
	 * $vid、$playname的获取方式
	 * 1.从影视大全的播放地址开始入手，如：http://www.yingshidaquan.cc/play/DQ220680-5-1.html
	 * 2.通过正则表达式 得到$index = 5, $subindex = 1; 然后$subindex --;
	 * 3.获取该播放也没的内容，解析ff_urls变量，该变量是一个数组
	 * 4.然后$play_info = $ff_urls[$index] 得到，其中playname就在该键值中
	 * 5.vid在$play_info['play_urls'][$subindex]中
	 */
	public function parse($url)
	{
		$url = explode('|', $url);
		$vid = $url[0];
		$playname = $url[1];

		if ($playname == 'superm3u8' && Lamb_Utils::isHttp($vid)) {
			$ret = $this->parse_byurl($vid, true);

			if (!is_array($ret)) {
				return false;
			}
			
			$vid = $ret['vid'];
			$playname = $ret['playname'];
		}
		
		if (preg_match('/bilibili|acfun/is', $vid)) {
			$path = 'macfun.php';
		} else if ($playname == 'mediahd' || $playname == 'qianmo'){
			$path = 'acfunu8.php';
		} else if ($playname == 'yuku') {
			$path = 'agent.php';
		} else {
			$path = 'mbshare.php';
		}
		$vid = urlencode($vid);
		
		if ($playname == 'yuku') {
			$url = "http://www.yingshidaquan.cc/player/js/dqplayer/common/agent.php?v={$vid}";
		} else {
		 	$url = "https://dqplayer.duapp.com/bshare/{$path}?v={$vid}";
		}
		
		$html = $this->httpsFetch($url);
		if (in_array($path, array('mbshare.php', 'acfunu8.php'))) {
			$patt = '/access\=\s*\'(.*?)\'.*?token\=\s*\'(.*?)\'.*?flashvars.*?a\:\'(.*?)\'/is';
		} else {
			$patt = '/access\=\s*\'(.*?)\'.*?flashvars.*?f\:\s*\'(.*?)\'/is';
		}

		if (preg_match($patt, $html, $result)) {
			$access = trim($result[1]);
			if (in_array($path, array('mbshare.php', 'acfunu8.php'))) {
				$token = trim($result[2]);
				$url = trim($result[3]);
			} else {
				$token = 'b34a61251443406704';
				$url = trim($result[2]);		
			}
			
			$url = "{$url}{$vid}&access={$access}&token={$token}&hd=2";
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
			$status = $client->getStatus();
			
			if ($status == 302)	{
				$url = @$headers['location'];
			} else if (preg_match('/<file><\!\[CDATA\[(.*?)\]\]>/is', $client->getContent(), $result)){
				$url = $result[1];
			} else {
				return false;
			}
		} else if (preg_match('/video\=\[\'(.*?)\'/is', $html, $result)){
			$url = $result[1];
		} else {
			return false;
		}
		
		if (empty($url)) {
			return false;
		}
		
		return array('normal' => array($url));	
	}
	
	public function httpsFetch($url)
	{
		$ch = curl_init();  
		curl_setopt($ch, CURLOPT_URL, $url);  
		curl_setopt($ch, CURLOPT_HEADER, false);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11');  
		  
		$res = curl_exec($ch);  
		$rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE);   
		curl_close($ch) ;	
		return $res;
	}
}