<?php
class Ttk_Parser_56 extends Ttk_Parser_Abstract
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
		if(!preg_match('/v\_(.*?)\./', $url, $matches)) {
			return false;
		}
		
		$vid = trim($matches[1]);

		$html = Lamb_Http::request(array(
			'url' => "http://m.56.com/view/id-{$vid}.html",
			'headers' => array(
				'user_agent' => 'Mozilla/5.0 (Linux; Android 4.3; Nexus 7 Build/JSS15Q) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2307.2 Safari/537.36'
			)
		), $status);			
		
		if (!$html || $status != 200) {
			return false;
		}
		
		if (!preg_match('/loadVxml\(\'(.*?)\'/is', $html, $result)) {
			return false;
		}
		
		$url = $result[1] . "&sohusvp=vaJy06j5oUqcl4_FIIiPH39t_XPgMDc7&callback=jsonp_dfInfo&tt=" . (time() * 1000);
		$html = Lamb_Http::quickGet($url, 5, false, $status);
		
		if (!preg_match('/"url"\:"(.*?)"/is', $html, $result)) {
			return false;
		}
		
		$url =  stripslashes($result[1]);

		return array('normal' => array($url));
	}		
	
}