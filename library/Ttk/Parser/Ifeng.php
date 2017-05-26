<?php
class Ttk_Parser_Ifeng extends Ttk_Parser_Abstract
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
		$html = Lamb_Http::quickGet($url, 5, false, $status);
		
		if (!$html || $status != 200) {
			return false;
		}
		
		if (!preg_match('/id"\s*\:\s*"(.*?)"/is', $html, $result)) {
			return false;
		}
		
		$html = Lamb_Http::quickGet("http://dyn.v.ifeng.com/cmpp/video_msg_ipad.js?msg={$result[1]}&param=playermsg&callback=jsonp3", 5, false, $status);
		
		if (!$html || $status != 200) {
			return false;
		}
		
		if (!preg_match('/(\{.*?\})/is', $html, $result)) {
			return false;
		}
		
		try {
			$data = json_decode($result[1], true);
		} catch (Exception $e) {
			return false;
		}
		
		return array('normal' => array($data['videoplayurl']));
	}		
	
}