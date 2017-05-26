<?php
class Ttk_Parser_Sohu extends Ttk_Parser_Abstract
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
		
		if (!preg_match('/vid\="(\d+)"/is', $html, $result)) {
			return false;
		}

		$url = "http://api.tv.sohu.com/video/playinfo/{$result[1]}.json?callback=&encoding=utf-8&api_key=f351515304020cad28c92f70f002261c&from=mweb";
		$html = Lamb_Http::quickGet($url, 5, false, $status);

		if (!$html || $status != 200) {
			return false;
		}
		
		try {
			$data = json_decode($html, true);
		} catch (Exception $e) {
			return false;
		}
	
		if (!$data || $data['status'] != 200) {
			return false;
		}
		
		$ret = array();

		foreach (array('url_super' => 'super', 'url_nor' => 'normal', 'url_high' => 'high') as $key => $val) {
			if (isset($data['data'][$key])) {
				$ret[$val] = array($data['data'][$key]);
			}
		}
		
		return $ret;
	}		
	
}