<?php
class Ttk_Parser_Letv extends Ttk_Parser_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function parse($url)
	{
		$ret = array();
		
		if(!preg_match('/vplay\/(\d{8})\.html/', $url, $matches)) {
			return false;
		}
		$vid = $matches[1];
		$tkey= $this->getTkey(time() * 1000 / 1e3);
		$data = Lamb_Http::quickGet("http://api.letv.com/mms/out/video/playJson?platid=3&splatid=301&tss=no&uid=40129856&id={$vid}&dvtype=1000&accessyx=1&domain=m.letv.com&tkey={$tkey}&callback=", 5, false, $status);
		
		if (!$data || $status != 200) {
			return false;
		}
		try {
			$data = json_decode($data, true);
		} catch (Exception $e) {
			return false;
		}
		$playUrls = $data['playurl']['dispatch'];
		
		foreach (array('high' => '1000', 'normal' => 'mp4') as $key => $val) {
			if (isset($playUrls[$val])) {
				$url = $data['playurl']['domain'][1] . $playUrls[$val][0] . '&format=1&jsonp=&expect=3&p1=0&p2=04&termid=2&ostype=android&hwtype=un&uuid=';
				$html = trim(Lamb_Http::quickGet($url, 5, false, $status));
				if ($html && $status == 200) {
					$html = substr($html, 1);
					$html = substr($html, 0, strlen($html) - 2);
					$html = json_decode($html, true);
					if ($html['status'] == 200) {
						$ret[$key] = $html['location'];
					}
				}
			}
		}
		
		return $ret;
	}	
	
	private function getTkey($stime)
	{
		$t = 185025305;
		$n = $t % 17;
		$r = $stime;
		
		for ($i = 0; $n > $i; $i++) {
			$j = 1 & $stime;
			$stime >>= 1;
			$j <<= 31;
			$stime += $j;
		}
		return $stime ^ $t;
	}	
}