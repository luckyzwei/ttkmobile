<?php
class Ttk_Parser_Pptv extends Ttk_Parser_Abstract
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
		
		if (!preg_match('/kk%3D(.*?)"/is', $html, $result)) {
			return false;
		}
		$kk = $result[1];
		
		if (!preg_match('/"id":(\d+)/is', $html, $result)) {
			return false;
		}		
		$id = $result[1];
		
		$url = "http://web-play.pptv.com/webplay3-0-{$id}.xml?version=4&type=mpptv&kk={$kk}&fwc=0&complete=1&o=www.flvurl.cn&rcc_id=www.flvurl.cn&cb=";
		
		$html = Lamb_Http::quickGet($url, 5, false, $status);
		
		if (!$html || $status != 200) {
			return false;
		}
		//print($html);exit;
		$xml = simplexml_load_string($html);
		$files = $xml->xpath('/root/channel/file/item');

		$ret = array();

		for ($i = 0; $i < min(3, count($files)); $i++) {
			$file = $files[$i]['rid'];
			$key = $i == 0 ? 'normal' : ($i == 1 ? 'high' : 'sd');
			
			$file = str_replace('.mp4', '.m3u8', $file);
			$server  = $xml->xpath("/root/dt[@ft='{$files[$i]['ft']}']");
			$server = $server[0];
			
			$ret[$key] = array("http://{$server->sh}/{$file}?type=mpptv&k={$server->key}");
		}
		
		if (!count($ret)) {
			return false;
		}
		
		return $ret;
	}		
	
}