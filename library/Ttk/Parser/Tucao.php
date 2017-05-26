<?php
class Ttk_Parser_Tucao extends Ttk_Parser_Abstract
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
		//letvc,qq,sina,tudou,youku
		$html = Lamb_Http::quickGet($url, 5, false, $status);
		
        if (!preg_match('/id="player_code".*?<li>(.*?)\|?<\/li>/is', $html, $matches) ) {
			return false;
		}
		
		if (!empty($matches)){
			//获得资源的数量
			$temp = explode('type', $matches[1]);
			$sourceNum = count($temp) - 1;
			
			if ($sourceNum < 1) {
				return false;
			}
			
			if ($sourceNum == 1) {//单集资源
				$play_data = trim($matches[1]);
			}else {//多集资源
				//获得资源的集数 
				if (!preg_match('~.*?#(\d+)~', $url, $number)){
					return false;
				}

				if (!preg_match_all('#type(.*?)\|+#', $matches[1], $source)) {
					return false;
				}
				//集数不能大于资源的数量
				if (!$source || count($source[1]) < $number[1]) {
					return false;
				}
				//去除'|'和空格
				$play_data = trim(str_replace('|', '', $source[0][$number[1]-1]));
			}		
			
			//获得影片类型
			if (!preg_match('/type=(.*?)&/', $play_data, $type)) {
				return false; 
			}
			
			if ($type[1] == 'tudou') {
				if (!preg_match('/vid=(\w+)/', $play_data, $vid)) {
					return false;
				}
				
				return array('normal' => "http://vr.tudou.com/v2proxy/v2.m3u8?debug=1&it=$vid[1]&st=2&pw=");
			}
			
			$api = "https://ssl.tucao.tv/api/playurl.php?{$play_data}&key=tucaocf7d242.cc&r=".time();
			$html = file_get_contents($api);
			
			if (preg_match_all('/<url><!\[CDATA\[(.*?)\]\]><\/url>/', $html, $play_source)) {
				return array('normal' => $play_source[1]);
			}else {
				return false;
			}
		}else{
			return false;
		}
		
	}
	
}