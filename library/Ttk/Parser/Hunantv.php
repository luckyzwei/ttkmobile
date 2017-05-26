<?php
class Ttk_Parser_Hunantv extends Ttk_Parser_Abstract
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
		if(!preg_match_all('/\/(\d+)\.html/', $url, $matches)) {
			return false;
		}
		
		$vid = trim($matches[1][0]);
		$html = Lamb_Http::quickGet("http://v.api.hunantv.com/player/video?video_id={$vid}", 5, false, $status);
		if (!$html || $status != 200) {
			return false;
		}
		
		try {
			$baseInfo=json_decode($html, true);
		} catch (Exception $e) {
			return false;
		}

		$ret = array();
		foreach ($baseInfo['data']['stream'] as $item) {
			$key = $item['name'] == '标清' ? 'normal' : ($item['name'] == '高清' ? 'high' : 'sd');
			
			$data = Lamb_Http::quickGet($item['url'], 5, false, $status);
			if ($data && $status == 200) {
				try{
					$data = json_decode($data, true);
				} catch (Exception $e) {
					continue;
				}
				
				if (preg_match('/\.fhv/is', $data['info'])) {
					$ret[$key] = array(preg_replace('/http\:\/\/.*?\./is', 'http://pccvideodn.', $data['info']));
				} else {
					$ret[$key] = array( $data['info']);//array(preg_replace('/http\:\/\/p.*?\./is', 'http://pccvideodn.', $data['info']));
				}
			}
		}
		
		if (!count($ret)) {
			return false;
		}
		
		return $ret;
	}		
	
}