<?php
class Ttk_Collect_Soku_Item extends Ttk_Collect_Soku_Abstract implements Ttk_Collect_ItemInterface
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public $sPatt = array(
		'name' => '/class="base_name"><h1>(.*?)</is',
		'pic' => '/class="p_thumb"><img class="" src="(.*?)"/is',
		'year' => '/class="base_pub">(\d+)</is',
		'extra' => '/class="base_what">(.*?)</is',
		'area' => '/地区:<\/label><span>(.*?)<\/span>/is',
		'actors' => '/(?:主演|主持人):<\/label>(.*?)<\/span>/is',
		'directors' =>'/(?:导演|电视台):<\/label>(.*?)<\/span>/is',
		'tag' => '/类型:<\/label><span>(.*?)<\/span>/is', 		
		'description' => '/class="intro">(.*?)<\/div>/is',
		'channel' => '/class="source(.*?)detail end-->/is',	
		'a_item' => '/class="check".*?title="(.*?)".*?input.*?title="(.*?)".*?paid.*?id="(.*?)"/is',
		'a_item_movie' => '/class="check".*?title="(.*?)".*?input.*?stypename="(.*?)".*?paid.*?id="(.*?)"/is',
		'a_item_other' => '/title="(.*?)".*?label title="(.*?)".*?paid.*?id="(.*?)"/is',
		'a_item_other_movie' => '/title="(.*?)".*?label.*?stypename="(.*?)".*?paid.*?id="(.*?)"/is',
		'a_it' => '/<a.*?href="(.*?)".*?>(.*?)</is'
	);
	
	/**
	 * @Ttk_Collect_ItemInterface implemtions
	 */	
	public function collect($url, $source = '', $mark = '')
	{
		$patt = $this->sPatt;
		$ret = array(
			'name' => '',
			'actors' => '不详',
			'directors' => '不详',
			'year' => 0,
			'area' => '其他',
			'tag'  => '',
			'type' => '',
			'description' => '',
			'is_end' => 0,
			'mark' => '',
			'update_time' => time(),
			'play_data' => ''
		);
				
		if (!Lamb_Utils::isHttp($url)) {
			return $ret;
		}
		
		if (! ($content = Lamb_Utils::fetchContentByUrlH($url))) {
			return $ret;
		}
		
		if (!isset($this->mTypeid)) {
			if (!preg_match('/class="base_what">(.*?)</is', $content, $result)) {
				return $ret;
			}		
			$result = trim($result[1]);
	
			if (strpos($result, '电影') !== false) {
				$this->setTypeid(1);
			} else if (strpos($result, '电视剧') !== false) {
				$this->setTypeid(2);
			}else if (strpos($result, '动漫') !== false) {
				$this->setTypeid(3);
			} else if (strpos($result, '综艺') !== false){
				$this->setTypeid(4);
			}  else {
				return $ret;			
			}
		}
		
		$ret['type'] = $this->mTypeid;
		
		//采集公共属性
		foreach (array('name', 'pic', 'area', 'actors', 'directors', 'year', 'tag', 'description', 'channel') as $key) {
			if (preg_match($patt[$key], $content, $result)) {
				$ret[$key] = trim($result[1]);
			}
		}
		
		if (!isset($ret['channel']) || empty($ret['channel'])) {
			return null;
		}
		
		if ($this->mTypeid == 4) {
			$ret['mark'] = $mark;
		}
		
		$site = '';
		$mark = '';
		if ($source == '') {
			$str_item_other = $this->mTypeid == 1 ? 'a_item_other_movie' : 'a_item_other';
			$str_item = $this->mTypeid == 1 ? 'a_item_movie' : 'a_item';
			if (preg_match_all($patt[$str_item_other], $ret['channel'], $result)) {
				$arr = $this->intersect(self::$defaultMedia, $result[1]); 
				if (empty($arr)) {
					return null;
				}
				$index = $arr[0];
				$i = array_search($index, $result[1]);
				$site = isset($result[3][$i]) ? $result[3][$i] : '';
				if ($this->mTypeid == 1) {
					$mark = isset($result[2][$i]) && $result[2][$i] != '' ? $result[2][$i] : '标清';
				}
			} else if(preg_match($patt[$str_item], $ret['channel'], $result)) {
				$index  = isset($result[1]) ? trim($result[1]) : '';
				$site = isset($result[3]) ? trim($result[3]) : '';
				if ($this->mTypeid == 1) {
					$mark  = isset($result[2]) && $result[2] != '' ? $result[2] : '标清';
				}
			}
		
			$source = isset(self::$defaultMedia[$index]) ? self::$defaultMedia[$index] : 0;
		} else {
			$site = self::$sChhanelSites[$source];
		}
		
		if (!$site || !$source) {
			return $ret;
		}
		
		if ($this->mTypeid == 1) {
			$ret['mark'] = $mark;
		}

		$ret['actors'] = $this->filterHtmlTag($ret['actors']);
		$ret['actors'] = str_replace("/" , " ", $ret['actors']);
		$ret['directors'] = $this->filterHtmlTag($ret['directors']);
		$ret['directors'] = str_replace("/" , " ", $ret['directors']);
		$ret['tag']    	  = str_replace("/" , " ", $this->filterHtmlTag($ret['tag']));
		$ret['description'] = $this->filterHtmlTag($ret['description']);
		$ret['description'] = str_replace(array("...", "显示详情") , "", $ret['description']);
		$ret['description'] = preg_replace('/[\n\r\t]/', '', $ret['description']);
		
		if (preg_match("/class='linkpanels $site'(.*?)<\/ul>/is", $content, $result)) {
			$ret['play_data'] = $result[1];
		}
		
		if (!$ret['play_data']) {
			return $ret;
		}
		
		$d = array();
		$play_data = array();
		$time = time();
		if (preg_match_all($patt['a_it'], $ret['play_data'], $result)) {
			//Lamb_Debuger::debug($result);
			foreach ($result[1] as $k => $item) {
				$d['play_data'] = $item;
				$d['source'] = $source;
				$d['time']  = $time;
				$d['num']   = $this->mTypeid == 1 ? 1 : $result[2][$k];
				$d['extra'] = $d['num'];
				
				if ($this->mTypeid == 1) {
					$arr = explode(" ", $result[2][$k]);
					$d['extra'] = str_replace("：", "", $arr[0]);
					$d['description'] = isset($arr[1]) ? trim($arr[1]) : '';
					if (isset($arr[2])){
						$d['description'] = $d['description'] . $arr[2];
					}
				}
				$play_data[] = $d;
			}
			
		}
				
		$ret['play_data'] = $play_data;
		unset($ret['channel']);
		return $ret;
	}

}