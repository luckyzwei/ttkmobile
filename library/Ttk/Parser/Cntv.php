<?php
class Ttk_Parser_Cntv extends Ttk_Parser_Abstract
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
		
		if (!preg_match('/contentid"\s*content="(.*?)"/is', $html, $result)) {
			return false;
		}
		
		$url = urlencode($url);
		$html = Lamb_Http::quickGet("http://vdn.apps.cntv.cn/api/getHttpVideoInfo.do?pid={$result[1]}&tz=-8&from=000tv&url={$url}&idl=32&idlr=32&modifyed=false", 5, false, $status);		
		
		if (!$html || $status != 200) {
			return false;
		}
		
		try {
			$data = json_decode($html, true);
		} catch (Exception $e) {
			return false;
		}
		
		if (!isset($data['video'])) {
			return false;
		}
		$map = array('lowChapters' => 'normal', 'chapters' => 'normal');
		$ret = array();

		foreach ($map as $key => $val) {
			if (!isset($data['video'][$key])) {
				continue;
			}
			
			$ret[$val] = array();
			foreach ($data['video'][$key] as $item) {
				$ret[$val][] = $item['url'];
			}
		}
		
		if (!count($ret)) {
			return false;
		}
		
		return $ret;
	}		
	
}