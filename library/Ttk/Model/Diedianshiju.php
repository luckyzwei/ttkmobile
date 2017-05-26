<?php
class Ttk_Model_Diedianshiju extends Ttk_Model_ItemAbstract
{
	public function __construct()
	{

	}
	
	public $sPatt = array(
		'name' => '/property="og:title" content="(.*?)"/is',
		'pic'  => '/property="og:image" content="(.*?)"/is',
 		'tag'  => '/property="og:video:class" content="(.*?)"/is',
		'area' => '/property="og:video:area" content="(.*?)"/is',
		'year' => '/property="og:video:release_date" content="(.*?)"/is',
		'actors' => '/property="og:video:actor" content="(.*?)"/is',
		'directors' => '/property="og:video:director" content="(.*?)"/is',
		'description' => '/class="tjuqing">(.*?)<\/div>/is',
		'channel' => '/class="play-list-box">.*?(youku\.gif|letv\.gif|flv\.gif|yun\.gif|sohu\.gif|acfun\.gif|bibibi\.gif|qq\.gif|tudou\.gif).*?class="play-list">(.*?)<\/p>/is',
		'a_item'  => '/<a.*?href="(.*?)".*?第(.*?)集/is',
		'total_num' => '/<span class="color">共(.*?)(集|话)/is'
	);
	
	public function collectItem($url, &$mark)
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
		
		foreach (array('name', 'pic', 'tag', 'area', 'year', 'directors', 'actors', 'description', 'total_num') as $key) {
			if (preg_match($patt[$key], $html, $result)) {
				$ret[$key] = trim($result[1]);
			}
		}
		
		$source = '';
		if (preg_match($patt['channel'], $html, $result)) {
			$source = trim($result[1]);
			if ($result[2] == '' || empty($result[2])) {
				return array();
			}
			$ret['channel'] = trim($result[2]); 
		}
		
		//Lamb_Debuger::debug($ret['total_num']);
		$total_num = $ret['total_num'];
		$ret['description'] = trim($this->filterHtmlTag($ret['description']));
		unset($ret['total_num']);
		
		if( array_key_exists($source, $this->diediao_media) ) {
			$source = $this->diediao_media[$source];
		}
	
		if (!Lamb_Utils::isInt($source, true)) {
			return array();
		}
		
		$d = array();
		$play_data = array();
		$time = time();
		if (preg_match_all($patt['a_item'], $ret['channel'], $result)){
			if (!isset($result[2]) || empty($result[2]) ) {
				return 11;
			}
			arsort($result[2]);
			$mark = $result[2][0];
			foreach ($result[1] as $k => $item) {
				$url = Ttk_Parser_Diediao::parse_byurl('http://www.diediao.com' . $item);
				$extends = explode('|', $url);
				if ($source == 3) {
					$url = 'http://www.letv.com/ptv/vplay/' . $extends[0] . '.html';
				} else if ($source == 5) {
					$url =  'http://v.youku.com/v_show/id_' . $extends[0];
				} else if ($source == 12 || $source == 17 || $source == 23) { //tudou | bilibili | acfun
					$url = $extends[0];
				}
			
				$d['play_data'] = $url;
				$d['source'] = $source;
				$d['time'] = $time;
				$d['num'] = $result[2][$k];
				$d['extra'] = $d['num'];
				$play_data[] = $d;
			}
		}
				
		$ret['play_data'] = $play_data;
		if ($total_num && $mark >= $total_num) {
			$ret['is_end'] = 1;
		}
		unset($ret['channel']);
		unset($mark);
		return $ret;
	}
	
}
