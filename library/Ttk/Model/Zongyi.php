<?php
class Ttk_Model_Zongyi extends Ttk_Model_ItemAbstract
{
	public function __construct()
	{

	}
		
	public $sPatt = array(
		'name'    => '/class="base_name"><h1>(.*?)<\/h1>/is',	                          		
		'pic'    => '/class="p_thumb"><img class="" src="(.*?)"/is', 								 	  								 	  	
		'tag'  => '/类型:<\/label><span>(.*?)<\/span>/is', 		
		'area'  => '/区:<\/label><span>(.*?)<\/span>/is', 		
		'year'    => '/class="base_pub">(.*?)<\/li>/is',	                          		
		'actors'  => '/主持人:<\/label>(.*?)<\/li>/is', 		
		'description' => '/class="intro">(.*?)<\/div>/is',
		'channel' => '/class="source(.*?)<!--detail end/is',
		'a_item' => '/class="check".*?title="(.*?)".*?input.*?title="(.*?)".*?paid.*?id="(.*?)"/is',
		'a_item_other' => '/title="(.*?)".*?label title="(.*?)".*?paid.*?id="(.*?)"/is',
		'a_it' => '/<a.*?href="(.*?)".*?>(.*?)</is',
	);
	
	public function collectItem($url, $mark, $source)
	{
		$patt = $this->sPatt;
		$ret = array(
			'name' => '',
			'actors' => '不详',
			'directors' => '不详',
			'tag' => '不详',
			'year' => 0,
			'area' => '其他',
			'update_time' => time(),
			'mark' => '',
			'description' => '不详',
			'play_data' => '',
			'is_end' => 0,
			'type' => 4
		);
		
		if (!Lamb_Utils::isHttp($url)) {
			return $ret;
		}
		
		if (! ($html = Lamb_Utils::fetchContentByUrlH($url))) {
			return $ret;
		}
		
		foreach (array('name', 'year', 'pic', 'actors', 'area', 'tag', 'description', 'channel') as $key) {
			if (preg_match($patt[$key], $html, $result)) {
				$ret[$key] = trim($result[1]);
			}
		}
		 
		
		if (!isset($ret['channel']) || empty($ret['channel'])) {
			return $ret;
		}
		
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
		
		if ($source == '') {
			return array();
		}
		
		if (!$site || !$source) {
			return array();
		} 
		 
		if (preg_match("/class='linkpanels $site'(.*?)<\/ul>/is", $html, $result)) {
			//Lamb_Debuger::debug($result);
			$ret['play_data'] = $result[1];
		}
				
		if (!$ret['play_data']) {
			return $ret;
		}
		
		$ret['mark'] = $mark;
		$ret['actors'] = $this->filterHtmlTag($ret['actors']);
		$ret['actors'] = str_replace("/" , " ", $ret['actors']);
		$ret['tag']    = str_replace("/" , " ", $ret['tag']);
		$ret['description'] = $this->filterHtmlTag($ret['description']);
		$ret['description'] = str_replace(array("...", "显示详情") , "", $ret['description']);
		$ret['description'] = preg_replace('/[\n\r\t]/', '', $ret['description']);
		
		
		$d = array();
		$play_data = array();
		$time = time();
		if (preg_match_all($patt['a_it'], $ret['play_data'], $result)) {
			$index = count($result[1]);
			foreach ($result[1] as $k => $item) {
				$d['play_data'] = $item;
				$d['source'] = $source;
				$d['time'] = $time;
				$d['num'] = $index--;
				
				$arr = explode(" ", $result[2][$k]);
				$d['extra'] = str_replace("：", "", $arr[0]);
				$d['description'] = isset($arr[1]) ? trim($arr[1]) : '';
				if (isset($arr[2])){
					$d['description'] = $d['description'] . $arr[2];
				}
				$play_data[] = $d;
			}
		}		
		
		$ret['play_data'] = $play_data;
		unset($ret['channel']);
		
		return $ret;
	}
	
	

	
}
