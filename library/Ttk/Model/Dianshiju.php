<?php
class Ttk_Model_Dianshiju extends Ttk_Model_ItemAbstract
{
	public function __construct()
	{

	}
	
	public $sPatt = array(
		'name' => '/class="base_name"><h1>(.*?)</is',
		'pic' => '/class="p_thumb"><img class="" src="(.*?)"/is',
 		'tag' => '/类型:<\/label>(.*?)<\/span>/is',
		'area' => '/地区:<\/label><span>(.*?)<\/span>/is',
		'year' => '/class="base_pub">(.*?)</is',
		'actors' => '/主演:<\/label>(.*?)<\/span>/is',
		'directors' => '/导演:<\/label>(.*?)<\/span>/is',
		'description' => '/剧情简介:<\/label>(.*?)<\/div>/is',
		'channel' => '/class="source(.*?)class="intro">/is',
		'a_item' => '/class="check".*?title="(.*?)".*?input.*?title="(.*?)".*?paid.*?id="(.*?)"/is',
		'a_item_other' => '/title="(.*?)".*?label title="(.*?)".*?paid.*?id="(.*?)"/is',
		'a_it' => '/<a.*?href="(.*?)".*?>(.*?)</is',
	);
	
	public function collectItem($url, $source)
	{
		$patt = $this->sPatt;
		$ret = array(
			'name' => '',
			'actors' => '不详',
			'directors' => '不详',
			'tag' => '',
			'area' => '其他',
			'update_time' => time(),
			'mark' => '',
			'year' => 0,
			'description' => '',
			'play_data' => '',
			'is_end' => 0,
			'type' => 2
		);
		
		if (!Lamb_Utils::isHttp($url)) {
			return $ret;
		}
		
		if (! ($html = Lamb_Utils::fetchContentByUrlH($url))) {
			return $ret;
		}
		
		foreach (array('name', 'pic', 'tag', 'area', 'year', 'directors', 'actors', 'description', 'channel') as $key) {
			if (preg_match($patt[$key], $html, $result)) {
				$ret[$key] = trim($result[1]);
			}
		}
		
		if (!isset($ret['channel']) || empty($ret['channel'])) {
			return $ret;
		}
		//Lamb_Debuger::debug($ret['channel']);
		
		$site = '';
		if ($source == '') {
			if (preg_match_all($patt['a_item_other'], $ret['channel'], $result)){
				$arr = $this->intersect($this->defaultMedia, $result[1]); 
				if (empty($arr)) {
					return $ret;
				}
				$index = $arr[0];
				$i = array_search($index, $result[1]);
				$site = isset($result[3][$i]) ? $result[3][$i] : '';
			} else if(preg_match($patt['a_item'], $ret['channel'], $result)){
				$index  = isset($result[1]) ? trim($result[1]) : '';
				$site = isset($result[3]) ? trim($result[3]) : '';
			}
		
			$source = isset($this->defaultMedia[$index]) ? $this->defaultMedia[$index] : 0;
		} else {
			$site = $this->chanel_sites[$source];
		}
		
		if (!$site || !$source) {
			return $ret;
		}

		$ret['actors'] = $this->filterHtmlTag($ret['actors']);
		$ret['actors'] = str_replace("/" , " ", $ret['actors']);
		$ret['directors'] = $this->filterHtmlTag($ret['directors']);
		$ret['directors'] = str_replace("/" , " ", $ret['directors']);
		$ret['tag']    	  = str_replace("/" , " ", $this->filterHtmlTag($ret['tag']));
		$ret['description'] = $this->filterHtmlTag($ret['description']);
		$ret['description'] = str_replace(array("...", "显示详情") , "", $ret['description']);
		$ret['description'] = preg_replace('/[\n\r\t]/', '', $ret['description']);
		
		if (preg_match("/class='linkpanels $site'(.*?)<\/ul>/is", $html, $result)) {
			$ret['play_data'] = $result[1];
		}
		
		$d = array();
		$play_data = array();
		$time = time();
		if (preg_match_all($patt['a_it'], $ret['play_data'], $result)) {
			//Lamb_Debuger::debug($result);
			foreach ($result[1] as $k => $item) {
				$d['play_data'] = $item;
				$d['source'] = $source;
				$d['time'] = $time;
				if (!isset($result[2][$k]) || !$result[2][$k]) {
					return null;
				}
				$d['num'] = $result[2][$k];
				$d['extra'] = $d['num'];
				$play_data[] = $d;
			}
		}
		
		$ret['play_data'] = $play_data;
		unset($ret['channel']);
		return $ret;
	}
	
}
