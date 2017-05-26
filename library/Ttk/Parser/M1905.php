<?php
class Ttk_Parser_M1905 extends Ttk_Parser_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function parse($url)
	{
		$url = urlencode($url);
		$url = "http://ttk.apifree.net/1905.jsp?url={$url}&type=1&id=ttk";
		$html = Lamb_Http::quickGet($url, 5, false, $status);

		if (!$html || $status != 200) {
			return false;
		}

		if (!preg_match_all('/<s\s*hd\="(.*?)".*?\[CDATA\[(.*?)\]\]\>/is', $html, $result, PREG_SET_ORDER)) {
			return false;
		}
		
		$ret = array();
		foreach ($result as $item) {
			$key = $item[1] == '流畅' ? 'normal' : ($item[1] == '标清' ? 'high' : 'sd');
			if ($item[2]) {
				$ret[$key] = explode('|', $item[2]);
			}
		}
		
		return $ret;	
	}
	
	/**
	 * @param string $url
	 * @return boolean | array
	 */
	public  function parse2($url)
	{
		$html = Lamb_Http::quickGet($url, 5, false, $status);
		
		if (!$html || $status != 200) {
			return false;
		}
		
		if (!preg_match('/vid\s*\:\s*"(\d+)"/is', $html, $result)) {
			return false;
		}

		$html = Lamb_Http::request(array(
			'url' => "http://www.1905.com/api/video/getmediainfoplus.php?jsoncallback=player_source_key_hdexpmp4i&id={$result[1]}&type=0&fr=www&source_key=mcfile",
			'headers' => array(
				'user_agent' => 'Mozilla/5.0 (Linux; Android 4.3; Nexus 7 Build/JSS15Q) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2307.2 Safari/537.36',
				'Referer' => $url,
				'Cookie' => '__uv_=8202153954; pvid=400650995685941;'
			)
		), $status);
	
		if (!$html || $status != 200) {
			return false;
		}
		
		if (!preg_match('/iosurl"\:"(.*?)"/is', $html, $result)) {
			return false;
		}
		$url = base64_decode($result[1]);
		
		return array('normal' => array($url));
	}		
	
}