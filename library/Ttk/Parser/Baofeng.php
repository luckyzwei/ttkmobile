<?php
class Ttk_Parser_Baofeng extends Ttk_Parser_Abstract
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
		$drama = 1;
		if (preg_match('/drama\=(\d+)/is', $url, $result)) {
			$drama = $result[1];
		}
		
		if (strpos($url, 'http://e') !== false) {
			$url = str_replace('http://e', 'http://m', $url);
		} else if (strpos($url, 'http://www') !== false) {
			$url = str_replace('http://www', 'http://m.hd', $url);
		} else if (strpos($url, 'http://f') !== false) {
			$url = str_replace('http://f', 'http://m', $url);
		}

		$html = Lamb_Http::request(array(
			'url' => $url,
			'headers' => array(
				'user_agent' => 'Mozilla/5.0 (Linux; Android 4.3; Nexus 7 Build/JSS15Q) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2307.2 Safari/537.36'
			)
		), $status);		
		
		if (!$html || $status != 200) {
			return false;
		}
		
		if (!preg_match('/movie_detail\s*\=\s*(\{.*?)\</is', $html, $result)) {
			return false;
		}
		
		$data = trim($result[1]);
			
		try {
			$data = json_decode($data, true);
		} catch (Exception $e) {
			return false;
		}
	
		$page = 1;
		if ($drama > 50) {
			$page = ceil($drama / 50);
			$drama = $drama - ($page - 1) * 50;
		}
		$drama --;
		
		$a = $data['info_pianyuan'][0]['aid'] % 500;
		$url = "http://minfo.baofeng.net/asp_c/{$data['info_wid']}/{$a}/{$data['info_pianyuan'][0]['aid']}-n-50-r-1-s-0-p-{$page}.json?callback=a";
		
		$html = Lamb_Http::quickGet($url, 5, false, $status);
		
		if (!$html || $status != 200) {
			return false;
		}

		$html = substr($html, 2);
		$html = substr($html, 0, strlen($html) - 1);
		
		try {
			$data = json_decode($html, true);
		} catch (Exception $e) {
			return false;
		}
		
		if (!$data || !isset($data['video_list']) || !isset($data['video_list'][$drama])) {
			return false;
		}
		
		$video_info = $data['video_list'][$drama];

		$html = Lamb_Utils::fetchContentByUrlC("http://rd.p2p.baofeng.net/queryvp.php?type=3&gcid={$video_info['iid']}&callback=a");

		if (!$html || $status != 200) {
			return false;
		}
		
		if (!preg_match('/ip\'\:\'(.*?)\'.*?port\'\:\'(\d+).*?path\'\:\'(.*?)\'.*?key\'\:\'(.*?)\'/is', $html, $result)) {
			return false;
		}
		
		$data = array(
			'ip' => $result[1],
			'port' => $result[2],
			'path' => $result[3],
			'key' => $result[4]
		);	
		
		$map = array(
			'b' => "0",
			'a' => "1",
			'o' => "2",
			'f' => "3",
			'e' => "4",
			'n' => "5",
			'g' => "6",
			'h' => "7",
			't' => "8",
			'm' => "9",
			'l' => ".",
			'c' => "A",
			'p' => "B",
			'z' => "C",
			'r' => "D",
			'y' => "E",
			's' => "F"		
		);
		$urls = array();
		
		foreach (explode(',', $data['ip']) as $ip) {
			$_ip = '';
			for($i = 0, $j = strlen($ip); $i < $j; $i++) {
				$_ip .= $map[substr($ip, $i, 1)];
			}
			
			$urls[] = "http://{$_ip}:{$data['port']}/{$data['path']}?key={$data['key']}&filelen={$video_info['size']}";
		}

		return array('normal' => array($urls[0]));
	}		
	
}