<?php
class Ttk_Parser_Sina extends Ttk_Parser_Abstract
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
		
		if (!preg_match('/ipad_vid\s*\:\s*\'(\d+)\'/is', $html, $result)) {
			return false;
		}
		
		return array('normal' => array("http://edge.v.iask.com.sinastorage.com/{$result[1]}.mp4"));
	}		
	
}