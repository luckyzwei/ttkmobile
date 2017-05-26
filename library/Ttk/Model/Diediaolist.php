<?php
class Ttk_Model_Diediaolist
{
	public $list = array(
		1 => 'http://www.diediao.com/vod-show-id-2-picm-1-p-',
		2 => 'http://www.diediao.com/vod-show-id-3-picm-1-p-'
	);
	
	public function __construct()
	{

	}
	
	/**
	 * $type = 1 电视剧 $type = 2 动漫
	 */
	public function getUrl($type = 1, $page = 1)
	{
		if ($type<0 || $type> 2) {
			$type = 1;
		}
		
		return $this->list[$type] . $page . '.html';
	}

	public function collect($url, $type = 1)
	{
		$ret = array();
		
		if ( !($html = Lamb_Utils::fetchContentByUrl($url))) {
			return $ret;
		}
		
		if (!preg_match_all('/class="play\-txt".*?href="(.*?)">(.*?)<.*?class="mod_version">(.*?)<\/span/is', $html, $result, PREG_SET_ORDER)) {
			return $ret;
		}
		
		foreach ($result as $key => $item) {
			if(preg_match('/共(.*?)集.*?已完结/is', trim($item[3]), $rets)) {
				$ret[$key]['mark'] = $rets[1];
			} else if (preg_match('/连载至(.*?)集/is', trim($item[3]), $rets)) {
				$ret[$key]['mark'] = $rets[1];
			} else {
				continue;
			}
			
			$ret[$key]['url'] = 'http://www.diediao.com' . trim($item[1]);
			$ret[$key]['name'] = str_replace(array('韩剧', '日剧', '台剧', '(台剧)', '电视剧', '(新加坡)'), '', trim($item[2]));  
		} 
		
		return $ret;
	}
}
