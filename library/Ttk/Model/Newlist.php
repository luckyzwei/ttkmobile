<?php
class Ttk_Model_Newlist
{
	public $list = array(
		1 => 'http://www.soku.com/channel/movielist_0_0_0_1_',
		2 => 'http://www.soku.com/channel/teleplaylist_2016_0_0_1_',
		3 => 'http://www.soku.com/channel/animelist_0_0_0_1_',
		4 => 'http://www.soku.com/channel/varietylist_0_0_0_1_'
	);
	
	public function __construct()
	{

	}
	
	public function getUrl($type = 1, $page = 1)
	{
		if ($type<0 || $type> 4) {
			$type = 1;
		}
		
		return $this->list[$type] . $page . '.html';
	}

	public function collect($url, $type = 1)
	{
		$ret = array();
		
		if ( !($html = Lamb_Utils::fetchContentByUrlH($url))) {
			return $ret;
		}
		
		if (!preg_match_all('/class="p_link"><a href="(.*?)".*?title="(.*?)".*?class="p_status">(.*?)class="p_ishd">.*?播放源.*?status="(.*?)"/is', $html, $result, PREG_SET_ORDER)) {
			return $ret;
		}		
		
		foreach ($result as $key => $item) {
			if (preg_match('/class="p_ispaid">/is', $item[3], $result)) {
				continue;
			}
			
			$ret[$key] = array(
				'url' => 'http://www.soku.com' . trim($item[1]),
				'name' => trim($item[2])
			);
			
			if ($type == 2 || $type == 3) {
				$ret[$key]['is_end'] = strpos($item[4], '集全') ? 1 : 0;
			} 
			
			if ($type == 2 || $type == 3 || $type == 4) {
				$ret[$key]['mark'] = str_replace(array('更新至', '集', '全'), '', trim($item[4]));
			}
		}
		
		return $ret;
	}
}
