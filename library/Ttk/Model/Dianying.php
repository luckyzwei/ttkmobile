<?php
class Ttk_Model_Dianying extends Ttk_Model_ItemAbstract
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
		'channel' => '/class="source(.*?)gotoplay end/is',
		'a_item' => '/class="check".*?title="(.*?)".*?input.*?stypename="(.*?)".*?paid.*?id="(.*?)"/is',
		'a_item_other' => '/title="(.*?)".*?label.*?stypename="(.*?)".*?paid.*?id="(.*?)"/is',
		'a_it' => '/<a.*?href="(.*?)"/is',
	);
	
	public $defaultMedia = array(
		'搜狐' => 1, '风行' => 16,  '乐视' => 3, 'bilibili' => 17, '爱奇艺' => 4, '优酷' => 5, 'CNTV' => 11,  '土豆' => 12, '响巢' => 8,'凤凰' => 14, '腾讯' => 13,  '芒果TV' => 7, 'PPTV' => 2,  'yinshidaquan' => 15
	);
	
	public function collectItem($url)
	{
		$patt = $this->sPatt;
		$ret = array(
			'name' => '',
			'actors' => '不详',
			'directors' => '不详',
			'tag' => '',
			'area' => '其他',
			'update_time' => time(),
			'mark' => '标清',
			'year' => 0,
			'description' => '暂无',
			'play_data' => '',
			'is_end' => 0,
			'type' => 1
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
			return null;
		}
		
		$site = '';
		$mark = '标清';
		$source = '';
		if (preg_match_all($patt['a_item_other'], $ret['channel'], $result)){
			$arr = $this->intersect($this->defaultMedia, $result[1]); 
			if (empty($arr)) {
				return $ret;
			}
			$index = $arr[0];
			$i = array_search($index, $result[1]);
			$site = isset($result[3][$i]) ? $result[3][$i] : '';
			$mark = isset($result[2][$i]) && $result[2][$i] != '' ? $result[2][$i] : '标清';
		} else if(preg_match($patt['a_item'], $ret['channel'], $result)){
			$index = isset($result[1]) ? trim($result[1]) : '';
			$site  = isset($result[3]) ? trim($result[3]) : '';
			$mark  = isset($result[2]) && $result[2] != '' ? $result[2] : '标清';
		}
	
		$source = isset($this->defaultMedia[$index]) ? $this->defaultMedia[$index] : 0;
		
		if (!$site || !$source) {
			return $ret;
		}

		$ret['mark'] = $mark;
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
		
		$play_data = array();
		$d = array();
		$time = time();
		if (preg_match_all($patt['a_it'], $ret['play_data'], $result)) {
			$d['play_data'] = $result[1][0];
			$d['source'] = $source;
			$d['time'] = $time;
			$d['num'] = 1;
			$d['extra'] = 1;
			$play_data[] = $d;
		}	
		
		$ret['play_data'] = $play_data;
		unset($ret['channel']);
		return $ret;
	}
	
}