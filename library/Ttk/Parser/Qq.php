<?php
class Ttk_Parser_Qq extends Ttk_Parser_Abstract
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
		$url = urlencode($url);
		$url = "http://ttk.apifree.net/qq.jsp?url={$url}&type=1&id=ttk";
		
		$html = Lamb_Http::quickGet($url, 5, false, $status);

		if (!$html || $status != 200) {
			return false;
		}
		
		if (!preg_match_all('/<s\s*hd\="(.*?)".*?\[CDATA\[(.*?)\]\]\>/is', $html, $result, PREG_SET_ORDER)) {
			return false;
		}
		
		$ret = array();
		foreach ($result as $item) {
			$key = $item[1] == '流畅' ? 'normal' : ($item[1] == '标清' ? 'high' : 'super');
			$ret[$key] = explode('|', $item[2]);
		}
		
		return $ret;
	}		
	
}