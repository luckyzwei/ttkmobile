<?php
class Ttk_Parser_Kankan extends Ttk_Parser_Abstract
{
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
		$html = Lamb_Http::quickGet(str_replace('http://vod', 'http://m', $url), 5, false, $status);
		
		if (!$html || $status != 200) {
			return false;
		}
		
		if (!preg_match('/surls\s*\:\s*\[\'http:\/\/.*?\/.*?\/(.*?)\//is', $html, $result)) {
			return false;
		}
		$guid = strtoupper($result[1]);
		
		$html = Lamb_Http::request(array(
			'url' => "http://mp4.cl.kankan.com/getCdnresource_flv?gcid={$guid}",
			'headers' => array(
				'user_agent' => 'Mozilla/5.0 (Linux; Android 4.3; Nexus 7 Build/JSS15Q) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2307.2 Safari/537.36'
			)
		), $status);
		
		if (!$html || $status != 200) {
			return false;
		}

		if (!preg_match('/ip\:"(.*?)".*?path\:"(.*?)".*?param1\:(\d+),param2\:(\d+)/is', $html, $result)) {
			return false;
		}
		
		$url = "http://{$result[1]}/{$result[2]}?key2={$result[4]}&key=" . md5("xl_mp43651{$result[3]}{$result[4]}");
		
		return array('normal' => array($url));
	}		
	
}