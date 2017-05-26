<?php
class Ttk_Parser_Iqiyi extends Ttk_Parser_Abstract
{
	private $w;
	
	private $_;
	
	public function __construct()
	{
		parent::__construct();
		$temp = microtime(true) * 1000;
		$temp = explode('.', $temp);
		
		$this->_ = $temp[0];
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

		if (!preg_match('/data\-player\-videoid\="(.*?)"/is', $html, $ret)) {
			return false;
		}
		$vid = trim($ret[1]);
				
		if (!preg_match('/data\-player\-tvid\="(.*?)"/is', $html, $ret)) {
			return false;
		}	
		$tvid = trim($ret[1]);
		
		$oScript = new COM("MSScriptControl.ScriptControl"); 
		$oScript->Language = "JavaScript"; 
		$oScript->AllowUI = false; 
		$oScript->AddCode(file_get_contents(DATA_PATH . 'iqiyi_parse_js.js')); 
		$this->w = $oScript->Run("weorjjigh", $tvid, $this->_);
		
		$refurl = urlencode(str_replace('www.iqiyi.com', 'm.iqiyi.com', $url) . ";2;&tim={$this->_}");
		$ts = $this->_ - 7;
		$req_url = "http://cache.m.iqiyi.com/jp/tmts/{$tvid}/{$vid}/?platForm=h5&rate=2&tvid={$tvid}&vid={$vid}&cupid=qc_100001_100186&type=mp4&qyid=2x1ljpecibf7vers5dqe4q9d&nolimit=0&qd_jsin=aGFoYQ%3D%3D&src=d846d0c32d664d32b6b54ea48997a589&sc={$this->w}&__refI={$refurl}&qd_wsz=MF8w&t={$ts}&__jsT=sgve&callback=jsonp1";
		
		$html = Lamb_Http::quickGet($req_url, 5, false, $status);
		if (!$html || $status != 200) {
			return false;
		}
		
		if (!preg_match('/"m3u"\:"(.*?)"/is', $html, $ret)) {
			return false;
		}		
		
		return array('normal' => array(stripslashes($ret[1])));
	}	
}