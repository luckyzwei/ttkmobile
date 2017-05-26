<?php
class Ttk_Model_Yingshidaquan extends Ttk_Model_ItemAbstract
{
	public function __construct()
	{

	}
	
	public function collectItem($url)
	{
		static $patt = array(
			'all_info' => '/class="info">(.*?)<\/div><\/div>.*?class="playendpage">(.*?)class="endpage".*?class="endtext">.*?<\/p>(.*?)<\/div>/is',
			'base_info' => '/<li>(.*?)<\/li>/is',
			'tag' => '/\'_blank\'>(.*?)</is',
			'area' => '/<\/span>(.*?)</is',
			'year' => '/<\/span>(.*?)&/is',
			'update_time' => '/<\/span>(.*?)&/is',
			'actors' => '/<\/span>(.*?)</is',
			'description' => '/(.*?)<a/is',
			'play_data' => array(
				'/<span>yuku<\/span>.*?title=\'?!先导预告\'.*?href=\'(.*?)\'/is',
				'/<p>.*?\[AcFun\].*?href=\'(.*?)\'/is',
				'/<p>.*?\[BiliBili\].*?href=\'(.*?)\'/is',
				'/<p>.*?\[Letv\].*?href=\'(.*?)\'/is',
				'/<p>.*?\[acLetv\].*?href=\'(.*?)\'/is',
				'/<p>.*?\[QianMo\].*?href=\'(.*?)\'/is',
			)
		);
		
		$ret = array(
			'actors' => '不详',
			'directors' => '不详',
			'tag' => '',
			'area' => '其他',
			'update_time' => time(),
			'year' => 0,
			'description' => '',
			'play_data' => '',
			'is_end' => 0,
			'type' => 1
		);
		
		if (!Lamb_Utils::isHttp($url)) {
			return $ret;
		}
		
		if (!($html = Lamb_Utils::fetchContentByUrl($url))) {
			return $ret;
		}
		
		if (!preg_match($patt['all_info'], $html, $html)) {
			return $ret;
		}
		
		if (
			preg_match($patt['play_data'][0], $html[2], $result) ||
			preg_match($patt['play_data'][1], $html[2], $result) ||
			preg_match($patt['play_data'][2], $html[2], $result) ||
			preg_match($patt['play_data'][3], $html[2], $result) ||
			preg_match($patt['play_data'][4], $html[2], $result) ||
			preg_match($patt['play_data'][5], $html[2], $result) 
		) {
			//Lamb_Debuger::debug($result);
			$ret['play_data'] = 'http://www.yingshidaquan.cc' . $result[1];
			
		} else {
			//Lamb_Debuger::debug('21');
			return false;
		}
		
		if (preg_match_all($patt['base_info'], $html[1], $baseInfo)) {	
			if (preg_match($patt['year'], $baseInfo[1][0], $result)){
				$ret['year'] = intval($result[1]);
			}
			if (preg_match_all($patt['tag'], $baseInfo[1][1], $result)){
				foreach ($result[1] as $i => $it) {
					$result[1][$i] = trim($it);
				}
				$ret['tag'] = implode(' ', $result[1]);
			}
			if (preg_match($patt['actors'], $baseInfo[0][2], $result)){
				$ret['actors'] = str_replace(',', ' ', trim($result[1]));
			}
			
			if (preg_match($patt['area'], $baseInfo[0][3], $result)){
				$ret['area'] = trim($result[1]);
			}
			if (preg_match($patt['update_time'], $baseInfo[0][4], $result)){
				$ret['update_time'] = strtotime(trim($result[1]));
			}
		} else {
			return false;
		}
		
		if (preg_match($patt['description'], $html[3], $result)){
			$ret['description'] = $this->filterHtmlTag($result[1]);
		}
		
		return $ret;
	}
	
	public function d($str)
	{
		Lamb_Debuger::debug($str);
	}
	
}