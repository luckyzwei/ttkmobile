<?php
class Ttk_Parser_Funshion extends Ttk_Parser_Abstract
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
		
		if (!preg_match('/vplay.videoid\s*\=\s*(\d+)/is', $html, $result)) {
			return false;
		}

		$url = "http://pm.funshion.com/v5/media/play/?id={$result[1]}&cl=mweb&uc=25";
		$html = Lamb_Http::quickGet($url, 5, false, $status);

		if (!$html || $status != 200) {
			return false;
		}
		
		try {
			$data = json_decode($html, true);
		} catch (Exception $e) {
			return false;
		}
		
		$keymap = array('tv' => 'normal', 'dvd' => 'normal', 'hd' => 'high', 'sdvd' => 'super');
		$ret = array();
		
		foreach ($data['mp4'] as $item) {
			if (array_key_exists($item['code'], $keymap)) {
				$ret[$keymap[$item['code']]] = array("http://jobsfe.funshion.com/play/v1/mp4/{$item['infohash']}.mp4");
			}
		}
		
		return $ret;
	}		
	
}